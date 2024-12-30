<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Log;
use App\Models\Mesin;
use App\Models\TrMasuk;
use App\Models\Category;
use App\Models\IsiPerball;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Exports\ExportExcell;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TrMasukController extends Controller
{
    public function index(Request $request)
    {
        // Ambil filter dari request
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d')); // Tanggal 1 bulan ini
        $endDate = $request->input('end_date', now()->format('Y-m-d')); // Tanggal hari ini
        $category = $request->input('category'); // Ambil filter kategori dari request

        // Log akses ke halaman index
        LogService::logActivity('Lihat Data', 'Transaksi Masuk', 'Melihat daftar transaksi masuk.', [
            'Tanggal Mulai' => $startDate,
            'Tanggal Akhir' => $endDate,
            'Kategori' => $category,
        ]);

        // Query dengan kondisi filter
        $tr_masuk = TrMasuk::with('isiPerball', 'category')
            ->when($startDate, function ($query, $startDate) {
                return $query->whereDate('tanggal', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                return $query->whereDate('tanggal', '<=', $endDate);
            })
            ->when($category, function ($query, $category) {
                return $query->where('category_id', $category);
            })
            ->orderBy('tanggal', 'desc')
            ->get(); // Ambil semua data tanpa paginate

        $request->session()->put('tr_masuk', $tr_masuk);

        // Ambil data untuk dropdown filter
        $categories = Category::all();

        // Implementasi Pagination Manual
        $perPage = $request->input('entriesPerPage', 5); // Ambil nilai dari dropdown atau default ke 5
        $currentPage = LengthAwarePaginator::resolveCurrentPage(); // Halaman saat ini
        $currentItems = collect($tr_masuk)->slice(($currentPage - 1) * $perPage, $perPage)->values(); // Data per halaman
        $paginatedData = new LengthAwarePaginator(
            $currentItems,
            count($tr_masuk), // Total item
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()] // URL dan query parameters
        );

        return view('pages.tr_masuk.index', [
            'tr_masuk' => $paginatedData, // Menggunakan hasil pagination manual
            'categories' => $categories,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'category' => $category // Pastikan untuk mengirimkan kategori juga
        ]);
    }

    // public function exportToExcel(Request $request)
    // {
    //     $validated = $request->validate([
    //         'start_date' => 'required|date|before_or_equal:end_date',
    //         'end_date' => 'required|date',
    //         'category' => 'nullable|exists:categories,id',
    //     ], [
    //         'start_date.before_or_equal' => 'Tanggal mulai harus lebih kecil atau sama dengan tanggal akhir.',
    //     ]);


    //     $startDate = $validated['start_date'];
    //     $endDate = $validated['end_date'];
    //     $category = $validated['category'] ?? null; // Tetapkan default null jika tidak ada input



    //     // Dapatkan nama kategori atau "Semua_Kategori"
    //     $categoryName = $category ? Category::find($category)->name : 'Semua Kategori';

    //     $fileName = "Laporan Transaksi Masuk {$startDate} to {$endDate}-{$categoryName}.xlsx";


    //     // Log akses ke halaman index
    //     LogService::logActivity('Cetak Excell', 'Transaksi Masuk', 'Cetak laporan Excell transaksi masuk.', [
    //         'Tanggal Mulai' => $startDate,
    //         'Tanggal Akhir' => $endDate,
    //         'Kategori' => $category,
    //         'Nama File' => $fileName,
    //     ]);

    //     return Excel::download(new ExportExcell($startDate, $endDate, $category), $fileName);
    // }

    public function exportToExcel(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date',
            'category' => 'nullable|exists:categories,id',
        ], [
            'start_date.before_or_equal' => 'Tanggal mulai harus lebih kecil atau sama dengan tanggal akhir.',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $category = $validated['category'] ?? null;

        // Ambil transaksi berdasarkan filter
        $transactions = TrMasuk::whereBetween('created_at', [$startDate, $endDate]);

        if ($category) {
            $transactions->where('category_id', $category);
        }

        $transactions = $transactions->get();

        // Cek apakah transaksi kosong
        if ($transactions->isEmpty()) {
            return redirect()->route('tr_masuk.index')
                ->with('error', 'Cetak Excell Gagal, Tidak ada data untuk periode dan kategori yang dipilih.');
        }

        // Dapatkan nama kategori atau "Semua_Kategori"
        $categoryName = $category ? Category::find($category)->name : 'Semua Kategori';

        $fileName = "Laporan Transaksi Masuk {$startDate} to {$endDate}-{$categoryName}.xlsx";

        // Log akses ke halaman index
        LogService::logActivity('Cetak Excel', 'Transaksi Masuk', 'Cetak laporan Excel transaksi masuk.', [
            'Tanggal Mulai' => $startDate,
            'Tanggal Akhir' => $endDate,
            'Kategori' => $category,
            'Nama File' => $fileName,
        ]);

        return Excel::download(new ExportExcell($startDate, $endDate, $category), $fileName);
    }

    public function create()
    {
        $categories = Category::all();
        $isiPerball = IsiPerball::all();

        return view(
            'pages.tr_masuk.create',
            [
                "categories" => $categories,
                "isiPerball" => $isiPerball,
            ]
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "tanggal" => "nullable|date",
            "masuk_ball" => "nullable|integer|min:0",
            "masuk_pcs" => "nullable|integer|min:0",
            "isi_perball_id" => "required",
            "category_id" => "required",
            "note" => "nullable",
        ]);

        // Validasi custom untuk memastikan jika salah satu (masuk_ball atau masuk_pcs) diisi
        // dan tidak boleh 0 (baik masuk_ball atau masuk_pcs)
        if (($request->masuk_ball == 0 && $request->masuk_pcs == 0) || ($request->masuk_ball == null && $request->masuk_pcs == null)) {
            return redirect()->back()->withErrors(['masuk_ball' => 'Salah satu dari Masuk Ball atau Masuk Pcs harus diisi dan tidak boleh 0.']);
        }

        // Validasi tambahan jika masuk_ball < 1, set ke 0 dan isi_perball_id ke 5
        if ($request->masuk_ball < 1 || is_null($request->masuk_ball)) {
            $validated['masuk_ball'] = 0;
            $validated['isi_perball_id'] = 5;
        }

        if ($request->masuk_ball >= 1 && $request->isi_perball_id == 5) {
            return redirect()->back()->withErrors(['isi_perball_id' => 'Jika Isian Ball diisi maka harusnya ini tidak boleh kosong.']);
        }

        // Ambil nilai 'name' dari tabel isi_perballs
        $isiPerballName = IsiPerball::find($validated['isi_perball_id'])->name;
        $isiPerballValue = intval($isiPerballName);

        if ($isiPerballValue === 0 && $isiPerballName !== '0') {
            return redirect()->back()->with('error', 'Nilai isi per ball tidak valid.');
        }

        // Hitung jumlah_masuk
        $masukBall = $validated['masuk_ball'] ?? 0;
        $masukPcs = $validated['masuk_pcs'] ?? 0;

        // Hitung jumlah_masuk
        $jumlahMasuk = ($masukBall * $isiPerballValue) + $masukPcs;

        // Simpan ke database
        TrMasuk::create([
            'tanggal' => $validated['tanggal'] ?? Carbon::now()->toDateString(),
            'masuk_ball' => $masukBall,
            'isi_perball_id' => $validated['isi_perball_id'],
            'masuk_pcs' => $masukPcs,
            'jumlah_masuk' => $jumlahMasuk,
            'category_id' => $validated['category_id'],
            'note' => $validated['note'],
        ]);


        LogService::logActivity('Create', 'Transaksi Masuk', 'Menambahkan transaksi masuk baru', [
            'Tanggal' => $validated['tanggal'] ?? Carbon::now()->toDateString(),
            'Masuk Ball' => $masukBall,
            'Masuk Pcs' => $masukPcs,
            'Isi Perball' => $validated['isi_perball_id'],
            'Kategori' => $validated['category_id'],
            'Note' => $validated['note'],
        ]);

        return redirect('/tr_masuk');
    }




    public function edit($id)
    {
        $tr_masuk = TrMasuk::findOrFail($id);
        $categories = Category::all();
        $isiPerball = IsiPerball::all();

        return view('pages.tr_masuk.edit', [
            "tr_masuk" => $tr_masuk,
            "categories" => $categories,
            "isiPerball" => $isiPerball,

        ]);
    }

    // 

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            "tanggal" => "nullable|date",
            "masuk_ball" => "nullable|integer|min:0",
            "masuk_pcs" => "nullable|integer|min:0",
            "isi_perball_id" => "required",
            "category_id" => "required",
            "note" => "nullable",
        ]);

        // Custom Validation: Ensure either 'masuk_ball' or 'masuk_pcs' is filled and not 0
        if (($request->masuk_ball == 0 && $request->masuk_pcs == 0) || ($request->masuk_ball == null && $request->masuk_pcs == null)) {
            return redirect()->back()->withErrors(['masuk_ball' => 'Salah satu dari Masuk Ball atau Masuk Pcs harus diisi dan tidak boleh 0.']);
        }

        // Validation if masuk_ball >= 1, isi_perball_id should not be 5
        if ($request->masuk_ball >= 1 && $request->isi_perball_id == 5) {
            return redirect()->back()->withErrors(['isi_perball_id' => 'Jika Ball diisi maka isi per ball tidak boleh 0']);
        }

        // Fetch IsiPerball and validate its existence
        $isiPerball = IsiPerball::find($validated['isi_perball_id']);
        if (!$isiPerball) {
            return redirect()->back()->with('error', 'Isi perball tidak ditemukan.');
        }
        $isiPerballName = $isiPerball->name;
        $isiPerballValue = intval($isiPerballName);

        if ($isiPerballValue === 0 && $isiPerballName !== '0') {
            return redirect()->back()->with('error', 'Nilai isi per ball tidak valid.');
        }

        // Calculate jumlah_masuk
        $masukBall = $validated['masuk_ball'] ?? 0;
        $masukPcs = $validated['masuk_pcs'] ?? 0;
        $jumlahMasuk = ($masukBall * $isiPerballValue) + $masukPcs;

        // Default tanggal if not provided
        $tanggal = $validated['tanggal'] ?? Carbon::now()->toDateString();

        try {
            // Mencari transaksi berdasarkan ID dan memuat relasi 'isiPerball' dan 'category'
            $tr_masuk = TrMasuk::with(['isiPerball', 'category'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            // Jika transaksi tidak ditemukan, arahkan kembali ke halaman sebelumnya dengan pesan kesalahan
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');
        }


        // Log old data
        $dataOld = [
            'Tanggal' => $tr_masuk->tanggal,
            'Pakai Ball' => $tr_masuk->masuk_ball,
            'Isi Perball' => $tr_masuk->isiPerball->name,  // Access the 'name' from the related model
            'Masuk Pcs' => $tr_masuk->masuk_pcs,
            'Jumlah Masuk' => $tr_masuk->jumlah_masuk,
            'Kategori' => $tr_masuk->category->name,  // Access the 'name' from the related model
            'Note' => $tr_masuk->note ?? "-",
        ];

        // Update transaction
        $tr_masuk->update([
            'tanggal' => $tanggal,
            'masuk_ball' => $masukBall,
            'isi_perball_id' => $validated['isi_perball_id'],
            'masuk_pcs' => $masukPcs,
            'jumlah_masuk' => $jumlahMasuk,
            'category_id' => $validated['category_id'],
            'note' => $validated['note'],
        ]);

        // Log new data
        $category = Category::find($validated['category_id']);
        $dataNew = [
            'Tanggal' => $tanggal,
            'Masuk Ball' => $masukBall,
            'Isi Perball' => $isiPerballName,
            'Masuk Pcs' => $masukPcs,
            'Jumlah Pakai' => $jumlahMasuk,
            'Kategori' => $category->name ?? null,
            'Note' => $validated['note'] ?? '-',
        ];

        // Log activity
        LogService::logActivity('Update', 'Transaksi Pakai', 'Mengupdate data transaksi Pakai baru.', [
            'Data Lama' => json_encode($dataOld, JSON_PRETTY_PRINT),
            'Data Baru' => json_encode($dataNew, JSON_PRETTY_PRINT),
        ]);

        return redirect('/tr_masuk')->with('success', 'Transaksi berhasil diperbarui.');
    }


    public function delete($id)
    {
        // Ambil transaksi yang akan dihapus
        $trMasuk = TrMasuk::findOrFail($id);

        // Hapus transaksi
        $trMasuk->delete();

        LogService::logActivity('Delete', 'Transaksi Masuk', 'Menghapus transaksi masuk.', [
            'Tanggal' => $trMasuk->tanggal,
            'Masuk Ball' => $trMasuk->masuk_ball,
            'Isi Perball' => $trMasuk->isi_perball_id,
            'Masuk Pcs' => $trMasuk->masuk_pcs,
            'Kategori' => $trMasuk->category_id,
            'Note' => $trMasuk->note,
        ]);

        return redirect('/tr_masuk');
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
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $categoryFilter = $request->input('category');  // Bisa null jika tidak ada kategori

        // Periksa apakah tanggal mulai lebih besar dari tanggal akhir
        if (Carbon::parse($startDate)->gt(Carbon::parse($endDate))) {
            return redirect()->route('tr_masuk.index')
                ->with('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
        }

        // Ambil data transaksi masuk sesuai filter, urutkan berdasarkan tanggal
        $tr_masuk = TrMasuk::whereBetween('tanggal', [$startDate, $endDate])
            ->when($categoryFilter, fn($query) => $query->where('category_id', $categoryFilter))
            ->orderBy('tanggal', 'asc')  // Urutkan berdasarkan tanggal
            ->get();

        // Validasi jika tidak ada transaksi yang ditemukan
        if ($tr_masuk->isEmpty()) {
            return redirect()->route('tr_masuk.index')
                ->with('error', 'Cetak Pdf Gagal, Tidak ada data untuk periode dan kategori yang dipilih.');
        }

        // Menentukan nama kategori untuk file
        $categoryName = $categoryFilter ? Category::find($categoryFilter)->name : 'Semua Kategori';

        // Format nama file
        $startFormatted = Carbon::parse($startDate)->format('d-m');
        $endFormatted = Carbon::parse($endDate)->format('d-m');
        $fileName = "Lap Transaksi Masuk {$startFormatted} s.d {$endFormatted} - {$categoryName}.pdf";

        // Generate PDF dengan data yang sudah difilter dan diurutkan
        $pdf = Pdf::loadView('pages.tr_masuk.pdf', [
            'data' => $tr_masuk,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'categoryName' => $categoryName,
        ]);

        $pdf->setOption('isHtml5ParserEnabled', true);

        // Footer dengan informasi user dan waktu
        $pdf->setOption('footer', 'Dicetak oleh: ' . Auth::user()->name . ' | Tanggal dan Waktu: ' . now()->format('d-m-Y H:i:s'));

        LogService::logActivity('Cetak Pdf', 'Transaksi Masuk', 'Cetak laporan Pdf transaksi masuk.', [
            'Tanggal Mulai' => $startDate,
            'Tanggal AKhir' => $endDate,
            'Kategori' => $categoryFilter,
            'Nama File' => $fileName
        ]);

        // Return PDF untuk di-download atau preview dengan nama file yang telah ditentukan
        return $pdf->stream($fileName); // Menggunakan stream untuk preview dengan nama file
    }
}
