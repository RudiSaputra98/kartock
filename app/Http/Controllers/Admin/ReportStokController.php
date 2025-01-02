<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\TrMasuk;
use App\Models\TrPakai;
use App\Models\Category;
use App\Services\LogService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ExcellReportStok;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportStokController extends Controller
{
    public function index(Request $request)
    {
        // Mendapatkan transaksi terakhir per kategori
        $endDate = $request->input('end_date', now()->toDateString());

        // Ambil data transaksi berdasarkan filter tanggal
        $transactions = $this->getTransactions($endDate);

        // Ambil transaksi terakhir per kategori
        $groupedData = $this->getLastTransactionPerCategory($transactions);

        LogService::logActivity('Lihat Data', 'Report Stok Akhir', 'Melihat daftar report stok akhir semua kategori', [
            'Laporan Stok Per Tanggal' => $endDate,
        ]);

        // Mengirim data ke view
        return view('pages.report_stok.index', [
            'groupedData' => $groupedData,
            'endDate' => $endDate,
        ]);
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

    public function pdf(Request $request)
    {
        // Mendapatkan tanggal akhir (default ke hari ini)
        $endDate = $request->input('end_date', now()->toDateString());

        // Ambil data transaksi berdasarkan filter tanggal
        $transactions = $this->getTransactions($endDate);

        // Ambil transaksi dalam rentang tanggal dan filter kategori
        $transactions = $this->getTransactions($endDate);

        if ($transactions->isEmpty()) {
            // Mengarahkan kembali dengan pesan error yang dapat muncul di halaman
            return redirect()->route('report.stok.index')
                ->with('error', 'Cetak Pdf Gagal, Tidak ada data untuk tanggal yang dipilih.');
        }


        // Ambil transaksi terakhir per kategori
        $groupedData = $this->getLastTransactionPerCategory($transactions);

        // Urutkan berdasarkan nama kategori
        $groupedData = $groupedData->sortBy(function ($data) {
            return $data['category']; // Mengurutkan berdasarkan nama kategori
        });

        // Format nama file
        $endFormatted = Carbon::parse($endDate)->format('d-m');
        $fileName = "Laporan Stok per {$endFormatted}.pdf";

        // Generate PDF dengan data yang sudah difilter dan diurutkan
        $pdf = Pdf::loadView('pages.report_stok.pdf', [
            'groupedData' => $groupedData,
            'endDate' => $endDate,
        ]);

        $pdf->setOption('isHtml5ParserEnabled', true);

        // Footer dengan informasi user dan waktu di dalam view PDF
        $pdf->setOption('footer', 'Dicetak oleh: ' . Auth::user()->name . ' | Tanggal dan Waktu: ' . now()->format('d-m-Y H:i:s'));

        LogService::logActivity('Cetak Pdf', 'Report Stok AKhir', 'Mencetak pdf report stok akhir semua kategori', [
            'Laporan Stok Per Tanggal' => $endDate,
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
            'end_date' => 'required|date|after_or_equal:start_date',
            'category' => 'nullable|exists:categories,id',
        ]);

        // Tetapkan nilai dari input yang sudah divalidasi
        $endDate = $validated['end_date'];
        $category = $validated['category'] ?? null;

        // Ambil transaksi dalam rentang tanggal dan filter kategori
        $transactions = $this->getTransactions($endDate);

        if ($transactions->isEmpty()) {
            // Mengarahkan kembali dengan pesan error yang dapat muncul di halaman
            return redirect()->route('report.stok.index')
                ->with('error', 'Cetak excell Gagal, Tidak ada data untuk tanggal yang dipilih.');
        }

        // Buat instance laporan Excel
        $excelReport = new ExcellReportStok($endDate, $category);


        // Format nama file
        $fileName = "Laporan Stok Per Tanggal {$endDate}.xlsx";

        LogService::logActivity('Cetak Excell', 'Report Stok Akhir', 'Mencetak Excell report stok akhir semua kategori', [
            'Laporan Stok Per Tanggal' => $endDate,
            'Nama File' => $fileName,
        ]);

        // Unduh file Excel
        return Excel::download($excelReport, $fileName);
    }
}
