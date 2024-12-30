<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\TrMasuk;
use App\Models\TrPakai;
use App\Models\Category;
use App\Services\LogService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExcellReportCategory;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportCategoryController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tanggal mulai dan akhir dari request, jika tidak ada maka default ke bulan ini
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());



        // Ambil filter kategori dari request
        $categoryFilter = $request->input('category', null);

        LogService::logActivity('Lihat Data', 'Report Kategori', 'Melihat daftar report harian per kategori', [
            'Tanggal Mulai' => $startDate,
            'Tanggal AKhir' => $endDate,
            'Kategori' => $categoryFilter,
        ]);

        // Ambil semua kategori untuk dropdown
        $categories = Category::all();

        // Ambil stok awal dan transaksi
        $initialStocks = $this->getInitialStocks($startDate, $categoryFilter);
        $transactions = $this->getTransactions($startDate, $endDate, $categoryFilter);

        // Kelompokkan transaksi berdasarkan kategori
        $groupedData = $this->groupTransactionsByCategory($transactions);

        // Ambil jumlah entri per halaman dari request, jika tidak ada set default 5
        $perPage = $request->input('entriesPerPage', 5);

        // Hitung data laporan berdasarkan kategori
        $reportData = $this->calculateReportDataByCategory($groupedData, $initialStocks, $perPage);

        // Kembalikan ke tampilan dengan data yang dibutuhkan
        return view('pages.report-category.index', compact(
            'reportData',
            'startDate',
            'endDate',
            'categoryFilter',
            'categories',
            'initialStocks'
        ));
    }

    public function pdf(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date',
            'category' => 'nullable|exists:categories,id',
        ], [
            'start_date.before_or_equal' => 'Tanggal mulai harus lebih kecil atau sama dengan tanggal akhir.',
        ]);

        // Ambil data dari request
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $categoryFilter = $validated['category'] ?? null;

        // Ambil stok awal
        $initialStocks = $this->getInitialStocks($startDate, $categoryFilter);

        // Ambil transaksi
        $transactions = $this->getTransactions($startDate, $endDate, $categoryFilter);

        // Kelompokkan transaksi berdasarkan kategori
        $groupedData = $this->groupTransactionsByCategory($transactions);

        $perPage = $request->input('entriesPerPage', 'all');

        // Hitung data laporan berdasarkan kategori tanpa pagination
        $reportData = $this->calculateReportDataByCategory($groupedData, $initialStocks, $perPage);

        // Validasi jika tidak ada transaksi
        if ($transactions->isEmpty()) {
            // Mengarahkan kembali dengan pesan error yang dapat muncul di halaman
            return redirect()->route('report.category.index')
                ->with('error', 'Cetak Pdf Gagal, Tidak ada data untuk periode dan kategori yang dipilih.');
        }

        // Tentukan nama kategori
        $categoryName = $categoryFilter
            ? Category::find($categoryFilter)->name ?? 'Kategori Tidak Diketahui'
            : 'Semua Kategori';

        // Nama file PDF
        $fileName = "Laporan Stok {$startDate} s.d {$endDate} - {$categoryName}.pdf";

        LogService::logActivity('Cetak Pdf', 'Report Kategori', 'Mencetak pdf report per kategori', [
            'Tanggal Mulai' => $startDate,
            'Tanggal AKhir' => $endDate,
            'Kategori' => $categoryFilter,
            'Nama File' => $fileName,
        ]);

        // Generate PDF
        $pdf = Pdf::loadView('pages.report-category.pdf', [
            'reportData' => $reportData, // Pastikan variabel dikirim ke view
            'startDate' => $startDate,
            'endDate' => $endDate,
            'categoryName' => $categoryName,
            'initialStocks' => $initialStocks, // Tambahkan ini jika digunakan di view
        ]);

        return $pdf->stream($fileName);
    }


    public function exportToExcel(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date',
            'category' => 'nullable|exists:categories,id',
        ], [
            'start_date.before_or_equal' => 'Tanggal mulai harus lebih kecil atau sama dengan tanggal akhir.',
        ]);

        // Tetapkan nilai dari input yang sudah divalidasi
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $category = $validated['category'] ?? null;

        // Dapatkan nama kategori atau default 'Semua Kategori'
        $categoryName = $category ? Category::find($category)->name : 'Semua Kategori';

        // Hitung initial stock berdasarkan kategori dan periode
        $initialStocks = $this->getInitialStocks($startDate, $category);


        $transactions = $this->getTransactions($startDate, $endDate, $category);

        // Validasi jika tidak ada transaksi
        if ($transactions->isEmpty()) {
            // Mengarahkan kembali dengan pesan error yang dapat muncul di halaman
            return redirect()->route('report.category.index')
                ->with('error', 'Cetak Excell gagal, Tidak ada data untuk periode dan kategori yang dipilih.');
        }

        // Buat instance laporan Excel
        $excelReport = new ExcellReportCategory($startDate, $endDate, $category, $initialStocks);

        // Format nama file
        $startDateFormatted = Carbon::parse($startDate)->format('Y-m-d');
        $endDateFormatted = Carbon::parse($endDate)->format('Y-m-d');
        $fileName = "Laporan Transaksi By Category {$startDateFormatted} to {$endDateFormatted} - {$categoryName}.xlsx";

        LogService::logActivity('Cetak Excell', 'Report Kategori', 'Mencetak Excell report per kategori', [
            'Tanggal Mulai' => $startDate,
            'Tanggal AKhir' => $endDate,
            'Kategori' => $categoryName,
            'Nama File' => $fileName,
        ]);

        // Unduh file Excel
        return Excel::download($excelReport, $fileName);
    }










    private function getInitialStocks($startDate, $categoryFilter = null)
    {
        // Ambil transaksi masuk (TrMasuk) dan keluar (TrPakai) sebelum tanggal filter
        $transactions = TrMasuk::selectRaw('category_id, SUM(jumlah_masuk) as total_masuk, 0 as total_pakai')
            ->where('tanggal', '<', $startDate)
            ->when($categoryFilter, fn($query) => $query->where('category_id', $categoryFilter))
            ->groupBy('category_id')
            ->union(
                TrPakai::selectRaw('category_id, 0 as total_masuk, SUM(jumlah_pakai) as total_pakai')
                    ->where('tanggal', '<', $startDate)
                    ->when($categoryFilter, fn($query) => $query->where('category_id', $categoryFilter))
                    ->groupBy('category_id')
            )
            ->get()
            ->groupBy('category_id')
            ->map(function ($transactions) {
                return $transactions->reduce(fn($carry, $transaction) => [
                    'total_masuk' => $carry['total_masuk'] + $transaction->total_masuk,
                    'total_pakai' => $carry['total_pakai'] + $transaction->total_pakai,
                ], ['total_masuk' => 0, 'total_pakai' => 0]);
            });

        return $transactions;
    }

    public function getTransactions($startDate, $endDate, $categoryFilter = null)
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
            ->orderBy('tanggal', 'asc')
            ->get();
    }

    private function groupTransactionsByCategory($transactions)
    {
        if ($transactions->isEmpty()) {
            return collect();
        }

        return $transactions->groupBy('category_id')->map(function ($items, $categoryId) {
            $categoryName = Category::find($categoryId)->name ?? 'Tidak Diketahui';

            return $items->groupBy('tanggal')->map(function ($dailyTransactions, $date) use ($categoryName) {
                return [
                    'tanggal' => $date,
                    'category_name' => $categoryName,
                    'tr_masuk' => $dailyTransactions->sum('tr_masuk'),
                    'tr_pakai' => $dailyTransactions->sum('tr_pakai'),
                ];
            });
        });
    }

    public function calculateReportDataByCategory($groupedData, $initialStocks, $perPage)
    {
        $reportData = collect();

        foreach ($groupedData as $categoryId => $data) {
            $initialStock = $initialStocks->get($categoryId, ['total_masuk' => 0, 'total_pakai' => 0]);
            $stock = $initialStock['total_masuk'] - $initialStock['total_pakai'];

            $categoryTransactions = collect();

            foreach ($data as $transaction) {
                $stock += ($transaction['tr_masuk'] ?? 0) - ($transaction['tr_pakai'] ?? 0);

                $categoryTransactions->push([
                    'tanggal' => $transaction['tanggal'],
                    'category_id' => $categoryId,
                    'category' => $transaction['category_name'],
                    'tr_masuk' => $transaction['tr_masuk'],
                    'tr_pakai' => $transaction['tr_pakai'],
                    'stok' => $stock,
                ]);
            }

            // Periksa jika "All" dipilih
            if ($perPage === 'all') {
                // Jika "All" dipilih, tampilkan seluruh data tanpa pagination
                $reportData->put($categoryId, $categoryTransactions);
            } else {
                // Jika tidak, lakukan pagination
                $paginatedData = new LengthAwarePaginator(
                    $categoryTransactions->forPage(LengthAwarePaginator::resolveCurrentPage(), $perPage),
                    $categoryTransactions->count(),
                    $perPage,
                    LengthAwarePaginator::resolveCurrentPage(),
                    ['path' => url()->current(), 'query' => request()->query()]
                );

                $reportData->put($categoryId, $paginatedData);
            }
        }

        return $reportData;
    }
}
