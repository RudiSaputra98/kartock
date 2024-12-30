<?php

namespace App\Exports;

use App\Models\TrMasuk;
use App\Models\TrPakai;
use App\Models\Category;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;    // Untuk header kolom
use Maatwebsite\Excel\Concerns\WithMapping;     // Untuk mapping data
use Maatwebsite\Excel\Concerns\WithEvents;      // Untuk custom event seperti AfterSheet
use Maatwebsite\Excel\Concerns\WithCustomStartCell; // Untuk start cell


class ExcellReport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithCustomStartCell
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $startDate;
    protected $endDate;
    protected $category;
    protected $initialStocks;

    public function __construct($startDate, $endDate, $category, $initialStocks)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->category = $category;
        $this->initialStocks = $initialStocks;
    }

    public function collection()
    {
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $categoryFilter = $this->category;

        // Ambil stok awal berdasarkan transaksi sebelum startDate
        $initialStocks = $this->getInitialStocks($startDate, $categoryFilter);

        // Query TrMasuk (filter berdasarkan tanggal dan kategori)
        $query1 = TrMasuk::whereBetween('tanggal', [$startDate, $endDate])
            ->when($categoryFilter, function ($query) use ($categoryFilter) {
                return $query->where('category_id', $categoryFilter);  // Filter kategori di sini
            })
            ->selectRaw('tanggal, category_id, SUM(jumlah_masuk) as tr_masuk, 0 as tr_pakai, 0 as stok')
            ->groupBy('tanggal', 'category_id');

        // Query TrPakai (filter berdasarkan tanggal dan kategori)
        $query2 = TrPakai::whereBetween('tanggal', [$startDate, $endDate])
            ->when($categoryFilter, function ($query) use ($categoryFilter) {
                return $query->where('category_id', $categoryFilter);  // Filter kategori di sini
            })
            ->selectRaw('tanggal, category_id, 0 as tr_masuk, SUM(jumlah_pakai) as tr_pakai, 0 as stok')
            ->groupBy('tanggal', 'category_id');

        // Gabungkan query
        $transactions = $query1->union($query2)
            ->orderBy('tanggal', 'asc')
            ->orderBy('category_id', 'asc') // Urutkan berdasarkan kategori
            ->get();


        // Array untuk menyimpan stok per kategori
        $stokKategori = [];

        $transactions = $transactions->map(function ($item) use (&$stokKategori, $initialStocks) {
            $kategoriId = $item->category_id;

            // Gunakan stok awal yang diteruskan dari controller
            if (!isset($stokKategori[$kategoriId])) {
                $stokKategori[$kategoriId] = $initialStocks[$kategoriId] ?? 0;
            }

            // Perhitungan stok
            $stokKategori[$kategoriId] = (int) $stokKategori[$kategoriId] + (int) ($item->tr_masuk - $item->tr_pakai);

            // Set nilai stok
            $item->stok = $stokKategori[$kategoriId];

            return $item;
        });

        return $transactions;
    }





    private function getInitialStocks($startDate, $categoryFilter = null)
    {
        // Ambil stok awal dari transaksi sebelum startDate
        $queryMasuk = TrMasuk::selectRaw('category_id, SUM(jumlah_masuk) as total_masuk')
            ->where('tanggal', '<', $startDate);

        $queryPakai = TrPakai::selectRaw('category_id, SUM(jumlah_pakai) as total_pakai')
            ->where('tanggal', '<', $startDate);

        // Jika ada filter kategori
        if ($categoryFilter) {
            $queryMasuk->where('category_id', $categoryFilter);
            $queryPakai->where('category_id', $categoryFilter);
        }

        // Gabungkan query untuk mendapatkan stok awal
        $masukData = $queryMasuk->groupBy('category_id')->get();
        $pakaiData = $queryPakai->groupBy('category_id')->get();

        $stocks = [];

        // Gabungkan data untuk menghitung stok
        foreach ($masukData as $masuk) {
            $stocks[$masuk->category_id] = $masuk->total_masuk ?? 0;
        }

        foreach ($pakaiData as $pakai) {
            if (isset($stocks[$pakai->category_id])) {
                $stocks[$pakai->category_id] -= $pakai->total_pakai ?? 0;
            } else {
                $stocks[$pakai->category_id] = - ($pakai->total_pakai ?? 0);
            }
        }

        return $stocks;
    }





    // **Kolom Header**
    public function headings(): array
    {
        return [
            'Tanggal',
            'Masuk (Pcs)',
            'Keluar (Pcs)',
            'Kategori',
            'Stok',
        ];
    }


    public function map($row): array
    {
        return [
            $row->tanggal,
            $row->tr_masuk,
            $row->tr_pakai,
            $row->category->name ?? '-',  // Nama kategori
            $row->stok == 0 ? '-' : $row->stok,
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
                $sheet->setCellValue('A1', 'Laporan Transaksi Harian');
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
                $sheet->getColumnDimension('A')->setWidth(12); // Kolom A
                $sheet->getColumnDimension('B')->setWidth(8); // Kolom B
                $sheet->getColumnDimension('C')->setWidth(8); // Kolom C
                $sheet->getColumnDimension('D')->setWidth(28); // Kolom D
                $sheet->getColumnDimension('E')->setWidth(8); // Kolom E


                $sheet->getStyle('A5:E5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'wrapText' => true,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);


                $sheet->getStyle('A5:E' . $sheet->getHighestRow())->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}
