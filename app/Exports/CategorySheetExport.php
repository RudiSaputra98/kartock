<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class CategorySheetExport implements FromCollection, WithHeadings, WithTitle, WithCustomStartCell, WithEvents

{
    protected $category;
    protected $transactions;
    protected $initialStocks;
    protected $startDate;
    protected $endDate;

    public function __construct($category, $transactions, $initialStocks, $startDate, $endDate = null)
    {
        $this->category = $category;
        $this->transactions = $transactions;
        $this->initialStocks = $initialStocks;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $stokKategori = [];

        // Kelompokkan transaksi berdasarkan tanggal
        $groupedByDate = $this->transactions->groupBy('tanggal');

        // Urutkan berdasarkan tanggal
        $groupedByDate = $groupedByDate->sortKeys();

        // Proses setiap grup transaksi berdasarkan tanggal yang sudah terurut
        return $groupedByDate->map(function ($items, $tanggal) use (&$stokKategori) {
            $categoryId = $items->first()->category_id; // Ambil category_id dari transaksi pertama di grup
            $masukTotal = $items->sum('tr_masuk'); // Jumlahkan semua transaksi masuk
            $keluarTotal = $items->sum('tr_pakai'); // Jumlahkan semua transaksi keluar

            // Hitung stok berdasarkan transaksi
            $stokKategori[$categoryId] = ($stokKategori[$categoryId] ?? $this->initialStocks[$categoryId] ?? 0)
                + $masukTotal - $keluarTotal;

            return [
                'tanggal' => $tanggal,
                'masuk' => $masukTotal,
                'keluar' => $keluarTotal,
                'kategori' => $this->category->name,
                'stok' => $stokKategori[$categoryId],
            ];
        });
    }

    public function headings(): array
    {
        return ['Tanggal', 'Masuk (Pcs)', 'Keluar (Pcs)', 'Kategori', 'Stok'];
    }

    public function title(): string
    {
        $categoryName = $this->category->name;

        // Hapus kata-kata yang tidak diinginkan
        $categoryName = str_replace(['Karung ', 'Bag ', ' Kgs'], '', $categoryName);

        // Kembalikan nama kategori yang sudah dibersihkan
        return $categoryName;
    }


    public function startCell(): string
    {
        return 'A6'; // Data mulai dari A6
    }



    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Menambahkan header kustom pada baris A1 hingga A4
                $sheet->setCellValue('A1', 'Laporan Transaksi by Kategori');
                $sheet->setCellValue('A2', 'Periode'); // A1 berisi label
                $sheet->setCellValue('B2', $this->startDate . ' - ' . $this->endDate); // B1 berisi nilai periode

                $sheet->setCellValue('A3', 'Kategori'); // A2 berisi label
                $categoryName = $this->category ? $this->category->first()->name : 'Semua Kategori';

                $sheet->setCellValue('B3', $categoryName); // B2 berisi nama kategori

                $sheet->setCellValue('A4', 'Stok Awal'); // A3 berisi label
                $stokAwal = isset($this->initialStocks[$this->category->id]) ? $this->initialStocks[$this->category->id] : 0;
                $sheet->setCellValue('B4', $stokAwal); // B3 berisi nilai stok awal



                // Format tebal untuk A1
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                ]);

                // Set alignment untuk B2 hingga B4 rata kiri
                $sheet->getStyle('B2:B4')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                    ],
                ]);

                // Format angka di kolom B (contoh Masuk, Keluar, dan Stok)
                $sheet->getStyle('B6:E' . $sheet->getHighestRow())->getNumberFormat()->setFormatCode('#,##');


                // Lebar kolom untuk kolom A sampai E
                $sheet->getColumnDimension('A')->setWidth(11);
                $sheet->getColumnDimension('B')->setWidth(10);
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(11);

                // Gaya untuk header data pada A6:E6
                $sheet->getStyle('A6:E6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'wrapText' => true,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Gaya untuk seluruh data setelah header
                $sheet->getStyle('A6:E' . $sheet->getHighestRow())->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}
