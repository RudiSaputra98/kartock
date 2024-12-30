<?php

namespace App\Exports;

use App\Models\TrMasuk;
use App\Models\TrPakai;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExcellReportCategory implements WithMultipleSheets
{
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

    /**
     * Membuat sheet terpisah untuk setiap kategori
     */
    public function sheets(): array
    {
        // Ambil transaksi berdasarkan tanggal dan kategori yang ditentukan
        $transactions = $this->getTransactions($this->startDate, $this->endDate, $this->category);

        // Kelompokkan transaksi berdasarkan kategori
        $groupedByCategory = $transactions->groupBy('category_id');

        // Ambil stok awal untuk kategori yang ada
        $initialStocks = $this->getInitialStocks($this->startDate);

        $sheets = [];

        // Membuat sheet terpisah untuk setiap kategori
        foreach ($groupedByCategory as $categoryId => $transactions) {
            // Temukan kategori berdasarkan ID
            $category = Category::find($categoryId);

            // Jika kategori ditemukan, buat sheet
            if ($category) {
                $sheets[] = new CategorySheetExport($category, $transactions, $initialStocks, $this->startDate, $this->endDate);
            } else {
            }
        }

        return $sheets;
    }

    /**
     * Mendapatkan data transaksi berdasarkan tanggal dan kategori
     */
    private function getTransactions($startDate, $endDate, $categoryFilter = null)
    {
        return TrMasuk::whereBetween('tanggal', [$startDate, $endDate])
            ->when($categoryFilter, fn($query) => $query->where('category_id', $categoryFilter))
            ->selectRaw('tanggal, category_id, SUM(jumlah_masuk) as tr_masuk, 0 as tr_pakai')
            ->groupBy('tanggal', 'category_id')
            ->union(
                TrPakai::whereBetween('tanggal', [$startDate, $endDate])
                    ->when($categoryFilter, fn($query) => $query->where('category_id', $categoryFilter))
                    ->selectRaw('tanggal, category_id, 0 as tr_masuk, SUM(jumlah_pakai) as tr_pakai')
                    ->groupBy('tanggal', 'category_id')
            )
            ->get();
    }

    /**
     * Mendapatkan stok awal untuk setiap kategori
     */
    private function getInitialStocks($startDate)
    {
        $masukData = TrMasuk::selectRaw('category_id, SUM(jumlah_masuk) as total_masuk')
            ->where('tanggal', '<', $startDate)
            ->groupBy('category_id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->category_id => $item->total_masuk]);

        $pakaiData = TrPakai::selectRaw('category_id, SUM(jumlah_pakai) as total_pakai')
            ->where('tanggal', '<', $startDate)
            ->groupBy('category_id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->category_id => $item->total_pakai]);

        // Hitung stok awal untuk setiap kategori
        return $masukData->map(fn($masuk, $id) => $masuk - ($pakaiData[$id] ?? 0));
    }
}
