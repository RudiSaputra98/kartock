<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\TrMasuk;
use App\Models\TrPakai;
use App\Models\Category;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Exports\ExcellReport;
use App\Exports\ExportExcell;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Ambil filter dari request
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $categoryFilter = $request->input('category', null);

        LogService::logActivity('Lihat Data', 'Report Harian', 'Melihat daftar report harian', [
            'Tanggal Mulai' => $startDate,
            'Tanggal AKhir' => $endDate,
            'Kategori' => $categoryFilter,
        ]);

        // Ambil daftar kategori untuk filter dropdown
        $categories = Category::all();

        // Ambil stok awal berdasarkan transaksi sebelum tanggal mulai
        $initialStocks = $this->getInitialStocks($startDate, $categoryFilter);

        // Ambil transaksi dalam rentang tanggal dan filter kategori
        $transactions = $this->getTransactions($startDate, $endDate, $categoryFilter);

        // Gabungkan data transaksi berdasarkan tanggal dan kategori
        $groupedData = $this->groupTransactions($transactions);

        // Hitung stok dan buat laporan
        $reportData = $this->calculateReportData($groupedData, $initialStocks);




        // Implementasi Pagination Manual
        $perPage = $request->input('entriesPerPage', 5); // Ambil nilai dari dropdown atau default ke 5
        $currentPage = LengthAwarePaginator::resolveCurrentPage(); // Halaman saat ini
        $currentItems = collect($reportData)->slice(($currentPage - 1) * $perPage, $perPage)->values(); // Data per halaman
        $paginatedData = new LengthAwarePaginator(
            $currentItems,
            count($reportData), // Total item
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()] // URL dan query parameters
        );

        return view('pages.report.index', compact('paginatedData', 'startDate', 'endDate', 'categoryFilter', 'categories'));
    }


    public function pdf(Request $request)
    {
        // Validasi input
        $request->validate([
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date',
        ], [
            'start_date.before_or_equal' => 'Tanggal mulai harus lebih kecil atau sama dengan tanggal akhir.',
        ]);


        // Ambil data transaksi berdasarkan filter dari request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $categoryFilter = $request->input('category');

        // Ambil stok awal berdasarkan transaksi sebelum tanggal mulai
        $initialStocks = $this->getInitialStocks($startDate, $categoryFilter);

        // Ambil transaksi dalam rentang tanggal dan filter kategori
        $transactions = $this->getTransactions($startDate, $endDate, $categoryFilter);

        if ($transactions->isEmpty()) {
            // Mengarahkan kembali dengan pesan error yang dapat muncul di halaman
            return redirect()->route('report.index')
                ->with('error', 'Cetak Pdf Gagal, Tidak ada data untuk periode dan kategori yang dipilih.');
        }

        // Gabungkan data transaksi berdasarkan tanggal dan kategori
        $groupedData = $this->groupTransactions($transactions);

        // Hitung stok dan buat laporan
        $reportData = $this->calculateReportData($groupedData, $initialStocks);

        // Menentukan nama kategori untuk file
        $categoryName = $categoryFilter ? Category::find($categoryFilter)->name : 'Semua Kategori';

        // Format nama file
        $startFormatted = Carbon::parse($startDate)->format('d-m');
        $endFormatted = Carbon::parse($endDate)->format('d-m');
        $fileName = "Laporan Stok {$startFormatted} s.d {$endFormatted} - {$categoryName}.pdf";

        // Generate PDF dengan data yang sudah difilter dan diurutkan
        $pdf = Pdf::loadView('pages.report.pdf', [
            'report' => $reportData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'categoryName' => $categoryName,
        ]);

        $pdf->setOption('isHtml5ParserEnabled', true);

        // Footer dengan informasi user dan waktu di dalam view PDF
        // Bisa ditambahkan di file 'pages.report.pdf' di bagian footer
        $pdf->setOption('footer', 'Dicetak oleh: ' . Auth::user()->name . ' | Tanggal dan Waktu: ' . now()->format('d-m-Y H:i:s'));


        LogService::logActivity('Cetak Pdf', 'Report Harian', 'Mencetak laporan pdf report harian', [
            'Tanggal Mulai' => $startDate,
            'Tanggal AKhir' => $endDate,
            'Kategori' => $categoryFilter,
            'Nama File' => $fileName,
        ]);

        // Menggunakan stream untuk preview atau download file PDF
        return $pdf->stream($fileName); // Preview PDF
        // return $pdf->download($fileName); // Untuk download file PDF
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
        $categoryName = $category ? Category::where('id', $category)->value('name') : 'Semua Kategori';

        $transactions = $this->getTransactions($startDate, $endDate, $category);

        // Validasi jika tidak ada transaksi
        if ($transactions->isEmpty()) {
            // Mengarahkan kembali dengan pesan error yang dapat muncul di halaman
            return redirect()->route('report.index')
                ->with('error', 'Cetak Excell gagal, Tidak ada data untuk periode dan kategori yang dipilih.');
        }

        // Hitung initial stock berdasarkan kategori dan periode
        $initialStocks = $this->getInitialStocks($startDate, $category);

        // Buat instance laporan Excel
        $excelReport = new ExcellReport($startDate, $endDate, $category, $initialStocks);

        // Format nama file
        $fileName = "Laporan Transaksi Harian {$startDate} to {$endDate} - {$categoryName}.xlsx";


        LogService::logActivity('Cetak Excell', 'Report Harian', 'Mencetak laporan excell report harian', [
            'Tanggal Mulai' => $startDate,
            'Tanggal AKhir' => $endDate,
            'Kategori' => $category,
            'Nama File' => $fileName,
        ]);

        // Unduh file Excel
        return Excel::download($excelReport, $fileName);
    }




    private function getInitialStocks($startDate, $categoryFilter = null)
    {
        $queryMasuk = TrMasuk::selectRaw('category_id, SUM(jumlah_masuk) as total_masuk, 0 as total_pakai')
            ->where('tanggal', '<', $startDate);

        $queryPakai = TrPakai::selectRaw('category_id, 0 as total_masuk, SUM(jumlah_pakai) as total_pakai')
            ->where('tanggal', '<', $startDate);

        if ($categoryFilter) {
            $queryMasuk->where('category_id', $categoryFilter);
            $queryPakai->where('category_id', $categoryFilter);
        }

        return $queryMasuk->groupBy('category_id')
            ->union($queryPakai->groupBy('category_id'))
            ->get()
            ->groupBy('category_id')
            ->map(function ($transactions) {
                return $transactions->reduce(function ($carry, $transaction) {
                    $carry['total_masuk'] += $transaction->total_masuk;
                    $carry['total_pakai'] += $transaction->total_pakai;
                    return $carry;
                }, ['total_masuk' => 0, 'total_pakai' => 0]);
            });
    }

    public function getTransactions($startDate, $endDate, $categoryFilter = null)
    {
        // Ambil transaksi berdasarkan rentang tanggal dan filter kategori
        $transactions = TrMasuk::whereBetween('tanggal', [$startDate, $endDate]);

        if ($categoryFilter) {
            // Pastikan categoryFilter adalah ID kategori yang valid
            $transactions->where('category_id', $categoryFilter);
        }

        // Query TrMasuk
        $query1 = $transactions->selectRaw('tanggal, category_id, SUM(jumlah_masuk) as tr_masuk, 0 as tr_pakai')
            ->groupBy('tanggal', 'category_id');

        // Query TrPakai
        $query2 = TrPakai::whereBetween('tanggal', [$startDate, $endDate])
            ->when($categoryFilter, function ($query) use ($categoryFilter) {
                return $query->where('category_id', $categoryFilter);
            })
            ->selectRaw('tanggal, category_id, 0 as tr_masuk, SUM(jumlah_pakai) as tr_pakai')
            ->groupBy('tanggal', 'category_id');

        // Gabungkan kedua query dengan UNION
        $transactions = $query1->union($query2)
            ->orderBy('tanggal', 'asc')
            ->get();

        return $transactions;
    }

    private function groupTransactions($transactions)
    {
        $groupedData = [];

        foreach ($transactions as $transaction) {
            if (!isset($transaction->category_id)) {
                continue;
            }

            $key = $transaction->category_id . '-' . $transaction->tanggal;

            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'tanggal' => $transaction->tanggal,
                    'category_id' => $transaction->category_id,
                    'category_name' => optional(Category::find($transaction->category_id))->name ?? 'Tidak Diketahui',
                    'tr_masuk' => 0,
                    'tr_pakai' => 0
                ];
            }

            $groupedData[$key]['tr_masuk'] += $transaction->tr_masuk;
            $groupedData[$key]['tr_pakai'] += $transaction->tr_pakai;
        }

        return $groupedData;
    }


    private function calculateReportData($groupedData, $initialStocks)
    {
        $reportData = [];
        $previousStok = $initialStocks->map(fn($item) => $item['total_masuk'] - $item['total_pakai'])->toArray();

        foreach ($groupedData as $data) {
            $categoryId = $data['category_id'];

            // Hitung stok awal kategori jika belum ada
            if (!isset($previousStok[$categoryId])) {
                $previousStok[$categoryId] = 0;
            }

            // Hitung stok berdasarkan transaksi selama periode filter
            $stok = $previousStok[$categoryId]
                + ($data['tr_masuk'] ?? 0)
                - ($data['tr_pakai'] ?? 0);

            // Dapatkan nama kategori
            $categoryName = optional(Category::find($categoryId))->name ?? 'Tidak Diketahui';

            // Simpan ke laporan
            $reportData[] = [
                'tanggal' => $data['tanggal'],
                'category' => $categoryName,
                'tr_masuk' => $data['tr_masuk'],
                'tr_pakai' => $data['tr_pakai'],
                'stok' => $stok,
            ];

            // Perbarui stok sebelumnya
            $previousStok[$categoryId] = $stok;
        }

        return $reportData;
    }
}
