<?php

namespace App\Exports;

use App\Models\TrMasuk;
use App\Models\TrPakai;
use App\Models\Category;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExcellReportStok implements FromCollection, WithHeadings, WithMapping, WithEvents, WithCustomStartCell
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $endDate;
    protected $category;

    public function __construct($endDate, $category)
    {
        $this->endDate = $endDate;
        $this->category = $category;
    }

    public function collection()
    {
        // Mendapatkan transaksi terakhir per kategori
        $endDate = $this->endDate; // Menggunakan $this->endDate

        // Ambil data transaksi berdasarkan filter tanggal
        $transactions = $this->getTransactions($endDate);

        // Ambil transaksi terakhir per kategori
        $groupedData = $this->getLastTransactionPerCategory($transactions);

        return $groupedData; // Mengembalikan data yang sudah dikelompokkan
    }

    private function getTransactions($endDate)
    {
        $transactions = TrMasuk::where('tanggal', '<=', $endDate) // Ambil semua data hingga tanggal akhir
            ->selectRaw('tanggal, category_id, SUM(jumlah_masuk) as tr_masuk, 0 as tr_pakai')
            ->groupBy('tanggal', 'category_id');

        $queryPakai = TrPakai::where('tanggal', '<=', $endDate) // Ambil semua data hingga tanggal akhir
            ->selectRaw('tanggal, category_id, 0 as tr_masuk, SUM(jumlah_pakai) as tr_pakai')
            ->groupBy('tanggal', 'category_id');

        // Gabungkan kedua query
        return $transactions->union($queryPakai)
            ->orderBy('tanggal', 'asc')
            ->get();
    }

    private function getLastTransactionPerCategory($transactions)
    {
        // Ambil semua kategori
        $categories = Category::all();

        // Ambil transaksi terakhir per kategori
        $groupedData = $categories->map(function ($category) use ($transactions) {
            $filteredTransactions = $transactions->where('category_id', $category->id);

            if ($filteredTransactions->isEmpty()) {
                return [
                    'tanggal' => null,
                    'category' => $category->name,
                    'stok' => 0,
                    'rincian' => '-' // Tidak ada transaksi, langsung minus
                ];
            }

            // Ambil transaksi terakhir
            $transaction = $filteredTransactions->sortByDesc('tanggal')->first();

            // Hitung stok
            $stok = $this->calculateStock($transaction);

            // Jika stok 0, langsung tampilkan minus
            if ($stok == 0) {
                return [
                    'tanggal' => $transaction->tanggal,
                    'category' => $category->name,
                    'stok' => $stok,
                    'rincian' => '-' // Tampilkan minus jika stok 0
                ];
            }

            // Tentukan pembagi berdasarkan kategori
            $divisor = strpos(strtolower($category->name), 'karung') !== false ? 500 : 20;
            $ball = floor($stok / $divisor); // Hitung jumlah ball
            $pcs = $stok % $divisor; // Sisa stok setelah dibagi
            $rincian = [];

            // Jika ball lebih dari 0, tampilkan ball
            if ($ball > 0) {
                $rincian[] = "{$ball} Ball";
            }

            // Jika ada pcs
            if ($pcs > 0) {
                $rincian[] = "{$pcs} Pcs";
            }

            // Gabungkan rincian
            return [
                'tanggal' => $transaction->tanggal,
                'category' => $category->name,
                'stok' => $stok,
                'rincian' => !empty($rincian) ? implode(' + ', $rincian) : '-' // Jika tidak ada ball dan pcs, tampilkan minus
            ];
        });

        return $groupedData;
    }


    private function calculateStock($transaction)
    {
        // Hitung jumlah masuk hingga tanggal transaksi
        $stokMasuk = TrMasuk::where('category_id', $transaction->category_id)
            ->where('tanggal', '<=', $transaction->tanggal)
            ->sum('jumlah_masuk');

        // Hitung jumlah pakai hingga tanggal transaksi
        $stokPakai = TrPakai::where('category_id', $transaction->category_id)
            ->where('tanggal', '<=', $transaction->tanggal)
            ->sum('jumlah_pakai');

        // Stok = jumlah masuk - jumlah pakai
        return $stokMasuk - $stokPakai;
    }

    // **Kolom Header**
    public function headings(): array
    {
        return [
            'Tanggal',
            'Kategori',
            'Stok',
            'Rincian',
        ];
    }


    public function map($row): array
    {
        return [
            $row['tanggal'] ?? '-',
            $row['category'] ?? '-',  // Nama kategori
            $row['stok'] == 0 ? '-' : $row['stok'],
            $row['rincian'],
        ];
    }

    // **Start Data dari Cell A5**
    public function startCell(): string
    {
        return 'A4';
    }

    // **Custom Header untuk A1 hingga A4**
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Header di A1 hingga A4
                $sheet->setCellValue('A1', 'Laporan Stok Akhir');
                $sheet->setCellValue('A2', 'Per Tanggal: ' . $this->endDate);

                // Format tebal untuk A1
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                ]);
                $sheet->getColumnDimension('A')->setWidth(12); // Kolom A
                $sheet->getColumnDimension('B')->setWidth(28); // Kolom B
                $sheet->getColumnDimension('C')->setWidth(9); // Kolom C
                $sheet->getColumnDimension('D')->setWidth(28); // Kolom D


                $sheet->getStyle('A4:D4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'wrapText' => true,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);


                $sheet->getStyle('A4:D' . $sheet->getHighestRow())->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}
