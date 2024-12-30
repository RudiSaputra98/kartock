<?php

namespace App\Exports;

use App\Models\TrMasuk;
use App\Models\Category;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportExcell implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $category;

    public function __construct($startDate, $endDate, $category)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->category = $category;
    }

    // **Data Collection**
    public function collection()
    {
        return TrMasuk::with(['category', 'isiPerball'])
            ->when($this->startDate, fn($query) => $query->whereDate('tanggal', '>=', $this->startDate))
            ->when($this->endDate, fn($query) => $query->whereDate('tanggal', '<=', $this->endDate))
            ->when($this->category, fn($query) => $query->where('category_id', $this->category)) // Hanya filter jika kategori ada
            ->get();
    }




    // **Kolom Header**
    public function headings(): array
    {
        return [
            'Tanggal',
            'Masuk Ball',
            'Isi Per Ball',     // Tambahkan kolom Isi Per Ball
            'Masuk Pcs',
            'Jumlah Masuk',
            'Kategori',         // Kolom Kategori menampilkan name
            'Catatan',
        ];
    }

    // **Mapping Data ke Kolom**
    public function map($row): array
    {
        return [
            $row->tanggal,
            $row->masuk_ball,
            $row->isiPerball->name ?? '-', // Isi Per Ball, default '-' jika null
            $row->masuk_pcs,
            $row->jumlah_masuk,
            $row->category->name ?? '-',  // Nama kategori, default '-' jika null
            $row->note,
        ];
    }

    // **Start Data dari Cell A5**
    public function startCell(): string
    {
        return 'A5';
    }

    // **Custom Header untuk A1 hingga A4**
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Header di A1 hingga A4
                $sheet->setCellValue('A1', 'Laporan Transaksi Masuk');
                $sheet->setCellValue('A2', 'Periode: ' . $this->startDate . ' - ' . $this->endDate);
                $categoryName = $this->category ? Category::find($this->category)->name : 'Semua Kategori';
                $sheet->setCellValue('A3', 'Kategori: ' . $categoryName);

                // Format tebal untuk A1
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                ]);
                $sheet->getColumnDimension('A')->setWidth(11); // Kolom A
                $sheet->getColumnDimension('B')->setWidth(8); // Kolom B
                $sheet->getColumnDimension('C')->setWidth(8); // Kolom C
                $sheet->getColumnDimension('D')->setWidth(8); // Kolom D
                $sheet->getColumnDimension('E')->setWidth(8); // Kolom E
                $sheet->getColumnDimension('F')->setWidth(25); // Kolom F
                $sheet->getColumnDimension('G')->setWidth(30); // Kolom G

                $sheet->getStyle('A5:G5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'wrapText' => true,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);


                $sheet->getStyle('A5:G' . $sheet->getHighestRow())->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}
