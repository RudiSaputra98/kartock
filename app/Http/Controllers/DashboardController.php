<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Note;
use App\Models\Mesin;
use App\Models\TrMasuk;
use App\Models\TrPakai;
use App\Models\Category;
use App\Models\IsiPerball;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        // Mendapatkan transaksi terakhir per kategori
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // Ambil data transaksi berdasarkan filter tanggal
        $transactions = $this->getTransactions($startDate, $endDate);

        // Ambil transaksi terakhir per kategori
        $groupedData = $this->getLastTransactionPerCategory($transactions);


        // Ambil catatan terbaru
        $notes = Note::latest()->get();

        // Ambil data pengguna yang sedang login
        $user = Auth::user();

        // Ambil data log terbaru
        $logs = Log::latest()->take(10)->get();

        // Format data log
        foreach ($logs as $log) {
            $jsonData = json_decode($log->data, true);
            $formattedData = '';

            // Format data berdasarkan kunci tertentu
            foreach ($jsonData as $key => $value) {
                if ($key == 'Isi Perball') {
                    $value = IsiPerball::find($value)->name ?? 'Nama tidak ditemukan';
                }
                if ($key == 'isi_perball_id') {
                    $value = IsiPerball::find($value)->name ?? 'Nama tidak ditemukan';
                }
                if ($key == 'Kategori') {
                    $value = Category::find($value)->name ?? 'Semua Kategori';
                }
                if ($key == 'Mesin') {
                    $value = Mesin::find($value)->name ?? 'Semua Mesin';
                }
                if ($key == 'mesin_id') {
                    $value = Mesin::find($value)->name ?? 'Semua Mesin';
                }
                if ($key == 'category') {
                    $value = Category::find($value)->name ?? 'Semua Kategori';
                }
                if ($key == 'category_id') {
                    $value = Category::find($value)->name ?? 'Semua Kategori';
                }

                // Gabungkan hasil format key dan value
                $formattedData .= "{$key}: {$value}\n";
            }

            // Hapus newline ekstra di akhir
            $log->formattedData = rtrim($formattedData);
        }


        // Mengirim data ke view
        return view('pages.dashboard.admin', [
            'groupedData' => $groupedData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'user' => $user,
            'notes' => $notes,
            'logs' => $logs, // Tambahkan formattedData di logs
        ]);
    }

    private function getAverageDailyUsage($categoryId)
    {
        // Ambil tanggal 30 hari yang lalu
        $thirtyDaysAgo = now()->subDays(30);

        // Ambil transaksi dalam 30 hari terakhir
        $last30DaysTransactions = TrPakai::where('category_id', $categoryId)
            ->where('tanggal', '>=', $thirtyDaysAgo)
            ->get();

        // Jika tidak ada transaksi
        if ($last30DaysTransactions->isEmpty()) {
            return 0; // Rata-rata penggunaan adalah 0
        }

        // Hitung total penggunaan dan jumlah hari unik
        $totalUsage = $last30DaysTransactions->sum('jumlah_pakai');
        $uniqueDays = $last30DaysTransactions->pluck('tanggal')->unique()->count();

        // Hitung rata-rata penggunaan per hari
        return $uniqueDays > 0 ? $totalUsage / $uniqueDays : 0;
    }

    private function calculateDaysLeft($stok, $categoryId)
    {
        // Ambil rata-rata penggunaan harian dari kategori
        $averageDailyUsage = $this->getAverageDailyUsage($categoryId);

        // Jika rata-rata tidak ada atau nol
        if ($averageDailyUsage <= 0) {
            return 'Tidak ada penggunaan';
        }

        // Hitung sisa hari
        $daysLeft = $stok / $averageDailyUsage;

        // Jika stok habis atau kurang dari 1 hari, kembalikan 0
        return $daysLeft > 0 ? round($daysLeft) : 0;
    }

    private function getTransactions($startDate, $endDate)
    {
        // Ambil transaksi berdasarkan rentang tanggal
        $transactions = TrMasuk::whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('tanggal, category_id, SUM(jumlah_masuk) as tr_masuk, 0 as tr_pakai')
            ->groupBy('tanggal', 'category_id');

        // Query TrPakai
        $queryPakai = TrPakai::whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('tanggal, category_id, 0 as tr_masuk, SUM(jumlah_pakai) as tr_pakai')
            ->groupBy('tanggal', 'category_id');

        // Gabungkan kedua query dengan UNION
        return $transactions->union($queryPakai)
            ->orderBy('tanggal', 'asc')
            ->get();
    }

    private function getLastTransactionPerCategory($transactions)
    {
        // Mengelompokkan transaksi berdasarkan kategori dan mengambil yang terakhir
        $groupedData = $transactions->groupBy('category_id')->map(function ($items) {
            // Ambil transaksi terakhir berdasarkan tanggal
            return $items->sortByDesc('tanggal')->first();
        });

        // Mengambil nama kategori dan menambahkan data transaksi terakhir per kategori
        return $groupedData->map(function ($transaction) {
            $category = Category::find($transaction->category_id);

            // Ambil data max_stok dan warning_stok dari kategori
            $maxStok = $category ? $category->max_stok : 0;
            $warningStok = $category ? $category->warning_stok : 0;
            $stok = $this->calculateStock($transaction);

            // Hitung persentase berdasarkan stok
            $stockPercentage = $this->calculateStockPercentage($transaction, $maxStok, $warningStok);
            $daysLeft = $this->calculateDaysLeft($stok, $transaction->category_id); // Tambahkan perhitungan Day Left
            return [
                'tanggal' => $transaction->tanggal,
                'category' => $category ? $category->name : 'Tidak Diketahui',
                'tr_masuk' => $transaction->tr_masuk,
                'tr_pakai' => $transaction->tr_pakai,
                'stok' => $this->calculateStock($transaction),
                'max_stok' => $maxStok,  // Menambahkan max stok
                'warning_stok' => $warningStok,  // Menambahkan warning stok
                'stockPercentage' => $stockPercentage,  // Persentase untuk progress bar
                'daysLeft' => $daysLeft, // Tambahkan kolom Day Left
            ];
        });
    }



    private function calculateStockPercentage($transaction, $maxStok, $warningStok)
    {
        $stok = $this->calculateStock($transaction);

        // Menghitung persentase berdasarkan stok dan max_stok
        $percentage = 0;
        if ($maxStok > 0) {
            $percentage = ($stok / $maxStok) * 140;
        }

        // Menentukan lebar progress bar berdasarkan kondisi stok
        if ($stok >= $maxStok) {
            return 100; // Hijau (100%)
        } elseif ($stok >= $warningStok) {
            return min(100, $percentage); // Kuning (60%) atau proporsional dengan max_stok
        } elseif ($stok > 0) {
            return min(100, $percentage); // Merah (20%) atau proporsional dengan max_stok
        } elseif ($stok <= ($warningStok * 0.4)) {
            return 5; // Hitam (5%)
        }

        return 0; // Jika stok 0 atau tidak terdefinisi
    }


    private function calculateStock($transaction)
    {
        // Hitung stok berdasarkan transaksi masuk dan pakai
        $initialStock = $this->getInitialStock($transaction->category_id);
        $stok = $initialStock;
        return $stok;
    }

    private function getInitialStock($categoryId)
    {
        // Ambil stok awal untuk kategori tertentu
        $initialMasuk = TrMasuk::where('category_id', $categoryId)->sum('jumlah_masuk');
        $initialPakai = TrPakai::where('category_id', $categoryId)->sum('jumlah_pakai');
        return $initialMasuk - $initialPakai;
    }
}
