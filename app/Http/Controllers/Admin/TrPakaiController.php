<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Mesin;
use App\Models\TrPakai;
use App\Models\Category;
use App\Models\IsiPerball;
use App\Exports\ExcellPakai;
use App\Services\LogService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;

class TrPakaiController extends Controller
{

    public function create()
    {
        $categories = Category::all();
        $isiPerball = IsiPerball::all();
        $mesin = Mesin::all();

        return view(
            'pages.tr_pakai.create',
            [
                "categories" => $categories,
                "isiPerball" => $isiPerball,
                "mesin" => $mesin,
            ]
        );
    }

    // public function pdf(Request $request)
    // {

    //     $validated = $request->validate([
    //         'start_date' => 'required|date|before_or_equal:end_date',
    //         'end_date' => 'required|date',
    //         'category' => 'nullable|exists:categories,id', // Pastikan category nullable
    //         'mesin' => 'nullable|exists:mesin,id', // Pastikan category nullable

    //     ], [
    //         'start_date.before_or_equal' => 'Cetak Pdf gagal, Tanggal mulai harus lebih kecil atau sama dengan tanggal akhir.',
    //     ]);

    //     // $startDate = $validated['start_date'];
    //     // $endDate = $validated['end_date'];
    //     // $categoryFilter = $validated['category'];
    //     // $mesin = $validated['mesin'];

    //     // Ambil data transaksi berdasarkan filter dari request
    //     $startDate = $request->input('start_date');
    //     $endDate = $request->input('end_date');
    //     $categoryFilter = $request->input('category');
    //     $mesin = $request->input('mesin');


    //     $tr_pakai = TrPakai::with('isiPerball', 'category', 'mesin')
    //         ->when($startDate, function ($query, $startDate) {
    //             return $query->whereDate('tanggal', '>=', $startDate);
    //         })
    //         ->when($endDate, function ($query, $endDate) {
    //             return $query->whereDate('tanggal', '<=', $endDate);
    //         })
    //         ->when($categoryFilter, function ($query, $category) {
    //             return $query->where('category_id', $category);
    //         })
    //         ->when($mesin, function ($query, $mesin) {
    //             return $query->where('mesin_id', $mesin);
    //         })
    //         ->orderBy('tanggal', 'asc')
    //         ->get();

    //     // Validasi jika tidak ada transaksi yang ditemukan
    //     if ($tr_pakai->isEmpty()) {
    //         return redirect()->route('tr_pakai.index')
    //             ->with('error', 'Cetak Pdf Gagal, Tidak ada data untuk periode dan kategori yang dipilih.');
    //     }

    //     // Menentukan nama kategori untuk file
    //     $categoryName = $categoryFilter ? Category::find($categoryFilter)->name : 'Semua Kategori';

    //     // Menentukan nama mesin untuk file
    //     $mesinName = $mesin ? Mesin::find($mesin)->name : 'Semua Mesin';

    //     // Format nama file
    //     $startFormatted = Carbon::parse($startDate)->format('d-m');
    //     $endFormatted = Carbon::parse($endDate)->format('d-m');
    //     $fileName = "Lap Transaksi Pakai {$startFormatted} s.d {$endFormatted} - {$categoryName} - {$mesinName}.pdf";

    //     // Generate PDF dengan data yang sudah difilter dan diurutkan
    //     $pdf = Pdf::loadView('pages.tr_pakai.pdf', [
    //         'data' => $tr_pakai,
    //         'startDate' => $startDate,
    //         'endDate' => $endDate,
    //         'categoryName' => $categoryName,
    //         'mesinName' => $mesinName,
    //     ]);

    //     $pdf->setOption('isHtml5ParserEnabled', true);

    //     // Footer dengan informasi user dan waktu
    //     // Jika 'footerHtml' tidak bekerja, coba tambahkan footer di view PDF (lihat di bawah)
    //     $pdf->setOption('footer', 'Dicetak oleh: ' . Auth::user()->name . ' | Tanggal dan Waktu: ' . now()->format('d-m-Y H:i:s'));

    //     LogService::logActivity('Cetak Pdf', 'Transaksi Pakai', 'Mencetak Laporan Pdf transaksi pakai.', [
    //         'Tanggal Mulai' => $startDate,
    //         'Tanggal AKhir' => $endDate,
    //         'Nama Kategori' => $categoryName,
    //         'Nama Mesin' => $mesinName,
    //         'Nama File' => $fileName
    //     ]);

    //     // Return PDF untuk di-download atau preview dengan nama file yang telah ditentukan
    //     return $pdf->stream($fileName); // Menggunakan stream untuk preview dengan nama file
    // }

    public function pdf(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date',
            'category' => 'nullable|exists:categories,id',
            'mesin' => 'nullable|exists:mesin,id',
        ], [
            'start_date.before_or_equal' => 'Cetak Pdf gagal, Tanggal mulai harus lebih kecil atau sama dengan tanggal akhir.',
        ]);

        // Retrieve the inputs
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $categoryFilter = $request->input('category');
        $mesin = $request->input('mesin');

        // Fetch transactions with filters applied
        $tr_pakai = TrPakai::with('isiPerball', 'category', 'mesin')
            ->when($startDate, function ($query, $startDate) {
                return $query->whereDate('tanggal', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                return $query->whereDate('tanggal', '<=', $endDate);
            })
            ->when($categoryFilter, function ($query, $category) {
                return $query->where('category_id', $category);
            })
            ->when($mesin, function ($query, $mesin) {
                return $query->where('mesin_id', $mesin);
            })
            ->orderBy('tanggal', 'asc')
            ->get();

        // Handle case where no transactions are found
        if ($tr_pakai->isEmpty()) {
            return redirect()->route('tr_pakai.index')
                ->with('error', 'Cetak Pdf Gagal, Tidak ada data untuk periode dan kategori yang dipilih.');
        }

        // Determine category and machine names, with fallback
        $categoryName = $categoryFilter ? Category::find($categoryFilter)->name ?? 'Kategori Tidak Ditemukan' : 'Semua Kategori';
        $mesinName = $mesin ? Mesin::find($mesin)->name ?? 'Mesin Tidak Ditemukan' : 'Semua Mesin';

        // Format file name with dates and names
        $startFormatted = Carbon::parse($startDate)->format('d-m');
        $endFormatted = Carbon::parse($endDate)->format('d-m');
        $fileName = "Lap Transaksi Pakai {$startFormatted} s.d {$endFormatted} - {$categoryName} - {$mesinName}.pdf";

        // Generate PDF
        $pdf = Pdf::loadView('pages.tr_pakai.pdf', [
            'data' => $tr_pakai,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'categoryName' => $categoryName,
            'mesinName' => $mesinName,
        ]);

        // Set additional PDF options
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('footer', 'Dicetak oleh: ' . Auth::user()->name . ' | Tanggal dan Waktu: ' . now()->format('d-m-Y H:i:s'));

        // Log activity
        LogService::logActivity('Cetak Pdf', 'Transaksi Pakai', 'Mencetak Laporan Pdf transaksi pakai.', [
            'Tanggal Mulai' => $startDate,
            'Tanggal AKhir' => $endDate,
            'Nama Kategori' => $categoryName,
            'Nama Mesin' => $mesinName,
            'Nama File' => $fileName
        ]);

        // Return PDF for preview or download
        return $pdf->stream($fileName);  // Use stream for preview
    }


    public function exportToExcel(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date',
            'category' => 'nullable|exists:categories,id', // Pastikan category nullable
            'mesin' => 'nullable|exists:mesin,id', // Pastikan category nullable

        ], [
            'start_date.before_or_equal' => 'Cetak Excell gagal, Tanggal mulai harus lebih kecil atau sama dengan tanggal akhir.',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $category = $validated['category'] ?? null; // Tetapkan default null jika tidak ada input
        $mesin = $validated['mesin'] ?? null;

        // Ambil transaksi berdasarkan filter
        $transactions = TrPakai::whereBetween('created_at', [$startDate, $endDate]);

        if ($category) {
            $transactions->where('category_id', $category);
        }

        if ($mesin) {
            $transactions->where('mesin_id', $mesin);
        }

        $transactions = $transactions->get();

        // Cek apakah transaksi kosong
        if ($transactions->isEmpty()) {
            return redirect()->route('tr_pakai.index')
                ->with('error', 'Cetak Excell Gagal, Tidak ada data untuk periode mesin dan kategori yang dipilih.');
        }

        $categoryName = $category ? Category::find($category)->name : 'Semua_Kategori';
        $mesinName = $mesin ? Mesin::find($mesin)->name : 'Semua_Mesin';

        $fileName = "Laporan Transaksi Pakai {$startDate} to {$endDate}-{$categoryName}-{$mesinName}.xlsx";

        LogService::logActivity('Cetak Excell', 'Transaksi Pakai', 'Mencetak Laporan Excell transaksi pakai.', [
            'Tanggal Mulai' => $startDate,
            'Tanggal AKhir' => $endDate,
            'Nama Kategori' => $categoryName,
            'Nama Mesin' => $mesinName,
            'Nama File' => $fileName
        ]);


        return Excel::download(new ExcellPakai($startDate, $endDate, $category, $mesin), $fileName);
    }


    public function index(Request $request)
    {
        // Ambil filter dari request
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d')); // Tanggal 1 bulan ini
        $endDate = $request->input('end_date', now()->format('Y-m-d')); // Tanggal hari ini
        $category = $request->input('category');
        $mesin = $request->input('mesin');

        // Log akses ke halaman index
        LogService::logActivity('Lihat Data', 'Transaksi Pakai', 'Melihat daftar transaksi Pakai.', [
            'Tanggal MUlai' => $startDate,
            'Tanggal AKhir' => $endDate,
            'Kategori' => $category,
            'Mesin' => $mesin,
        ]);


        // Query dengan kondisi filter
        $tr_pakai = TrPakai::with('isiPerball', 'category', 'mesin')
            ->when($startDate, function ($query, $startDate) {
                return $query->whereDate('tanggal', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                return $query->whereDate('tanggal', '<=', $endDate);
            })
            ->when($category, function ($query, $category) {
                return $query->where('category_id', $category);
            })
            ->when($mesin, function ($query, $mesin) {
                return $query->where('mesin_id', $mesin);
            })
            ->orderBy('tanggal', 'desc')
            ->get(); // Ambil semua data tanpa paginate

        // Ambil data untuk dropdown filter
        $categories = Category::all();
        $mesins = Mesin::all();

        // Implementasi Pagination Manual
        $perPage = $request->input('entriesPerPage', 5); // Ambil nilai dari dropdown atau default ke 5
        $currentPage = LengthAwarePaginator::resolveCurrentPage(); // Halaman saat ini
        $currentItems = collect($tr_pakai)->slice(($currentPage - 1) * $perPage, $perPage)->values(); // Data per halaman
        $paginatedData = new LengthAwarePaginator(
            $currentItems,
            count($tr_pakai), // Total item
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()] // URL dan query parameters
        );

        return view('pages.tr_pakai.index', [
            'tr_pakai' => $paginatedData, // Menggunakan hasil pagination manual
            'categories' => $categories,
            'mesins' => $mesins,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }


    public function store(Request $request)
    {
        // dd($request->all());

        $validated = $request->validate([
            "tanggal" => "nullable|date",
            "pakai_ball" => "nullable|integer|min:0",
            "pakai_pcs" => "nullable|integer|min:0",
            "isi_perball_id" => "required",
            "category_id" => "required",
            "reject" => "nullable",
            "mesin_id" => "required",
            "note" => "nullable",
        ]);

        // Periksa apakah pakai_ball, pakai_pcs, atau reject terisi
        if (
            ($request->pakai_ball == null || $request->pakai_ball == 0) &&
            ($request->pakai_pcs == null || $request->pakai_pcs == 0) &&
            ($request->reject == null || $request->reject == 0)
        ) {
            return redirect()->back()->withErrors([
                'pakai_ball' => 'Salah satu dari Pakai Ball, Masuk Ball, atau Reject harus diisi dan tidak boleh 0.',
            ]);
        }

        // Validasi tambahan jika pakai_ball < 1, set ke 0 dan isi_perball_id ke 5
        if ($request->pakai_ball < 1 || is_null($request->pakai_ball)) {
            $validated['pakai_ball'] = 0;
            $validated['isi_perball_id'] = 11;
        }

        if ($request->pakai_ball >= 1 && $request->isi_perball_id == 11) {
            return redirect()->back()->withErrors(['isi_perball_id' => 'Jika Isian Ball diisi maka harusnya ini tidak boleh kosong.']);
        }

        // Ambil nilai 'name' dari tabel isi_perballs
        $isiPerballName = IsiPerball::find($validated['isi_perball_id'])->name;
        $isiPerballValue = intval($isiPerballName);

        if ($isiPerballValue === 0 && $isiPerballName !== '0') {
            return redirect()->back()->with('error', 'Nilai isi per ball tidak valid.');
        }

        // Hitung jumlah_pakai
        $pakaiBall = $validated['pakai_ball'] ?? 0;
        $pakaiPcs = $validated['pakai_pcs'] ?? 0;
        $reject = $validated['reject'] ?? 0;

        // Hitung jumlah_pakai
        $jumlahPakai = ($pakaiBall * $isiPerballValue) + $pakaiPcs + $reject;

        // Simpan ke database
        TrPakai::create([
            'tanggal' => $validated['tanggal'] ?? Carbon::now()->toDateString(),
            'pakai_ball' => $pakaiBall,
            'isi_perball_id' => $validated['isi_perball_id'],
            'pakai_pcs' => $pakaiPcs,
            'jumlah_pakai' => $jumlahPakai,
            'category_id' => $validated['category_id'],
            'reject' => $reject,
            'mesin_id' => $validated['mesin_id'],
            'note' => $validated['note'],
        ]);

        // Log akses ke halaman index
        LogService::logActivity('Create', 'Transaksi Pakai', 'menambahkan transaksi Pakai baru.', [
            'Tanggal' => $validated['tanggal'] ?? Carbon::now()->toDateString(),
            'Pakai Ball' => $pakaiBall,
            'Isi Perball' => $validated['isi_perball_id'],
            'Pakai Pcs' => $pakaiPcs,
            'Reject' => $reject,
            'Jumlah Pakai Ball + Pcs' => $jumlahPakai,
            'Kategori' => $validated['category_id'],
            'Mesin' => $validated['mesin_id'],
            'Note' => $validated['note'],
        ]);

        return redirect('/tr_pakai');
    }



    public function edit($id)
    {
        $tr_pakai = TrPakai::findOrFail($id);
        $categories = Category::all();
        $isiPerball = IsiPerball::all();
        $mesin = Mesin::all();

        return view('pages.tr_pakai.edit', [
            "tr_pakai" => $tr_pakai,
            "categories" => $categories,
            "isiPerball" => $isiPerball,
            "mesin" => $mesin,

        ]);
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            "tanggal" => "nullable|date",
            "pakai_ball" => "nullable|integer|min:0",
            "pakai_pcs" => "nullable|integer|min:0",
            "isi_perball_id" => "required",
            "category_id" => "required",
            "reject" => "nullable|integer|min:0",
            "mesin_id" => "required",
            "note" => "nullable",
        ]);

        // Periksa apakah pakai_ball, pakai_pcs, atau reject terisi
        if (
            ($request->pakai_ball == null && $request->pakai_pcs == null && $request->reject == null) ||
            ($request->pakai_ball == 0 && $request->pakai_pcs == 0 && $request->reject == 0)
        ) {
            return redirect()->back()->withErrors([
                'pakai_ball' => 'Salah satu dari Pakai Ball, Masuk Ball, atau Reject harus diisi dan tidak boleh 0.',
            ]);
        }

        // Validasi tambahan jika pakai_ball >= 1, isi_perball_id tidak boleh 5
        if ($request->pakai_ball >= 1 && $request->isi_perball_id == 11) {
            return redirect()->back()->withErrors(['isi_perball_id' => 'Jika Ball diisi maka isi per ball tidak boleh 0']);
        }

        // Ambil nilai 'name' dari tabel isi_perballs dengan pengecekan jika tidak ada data
        $isiPerball = IsiPerball::find($validated['isi_perball_id']);
        if (!$isiPerball) {
            return redirect()->back()->with('error', 'Isi per ball tidak ditemukan.');
        }
        $isiPerballName = $isiPerball->name;
        $isiPerballValue = intval($isiPerballName);

        if ($isiPerballValue === 0 && $isiPerballName !== '0') {
            return redirect()->back()->with('error', 'Nilai isi per ball tidak valid.');
        }

        // Hitung jumlah_pakai
        $pakaiBall = $validated['pakai_ball'] ?? 0;
        $pakaiPcs = $validated['pakai_pcs'] ?? 0;
        $reject = $validated['reject'] ?? 0;

        // Hitung jumlah_pakai
        $jumlahPakai = ($pakaiBall * $isiPerballValue) + $pakaiPcs + $reject;

        // Ambil nilai tanggal baru dari request, jika kosong tetap dengan tanggal lama
        $tanggal = $validated['tanggal'] ?? Carbon::now()->toDateString();  // Default ke tanggal hari ini jika tidak ada input

        // Gunakan findOrFail untuk mengambil model berdasarkan ID
        $trPakai = TrPakai::findOrFail($id);

        // Ambil nama untuk data lama
        $dataOld = [
            'Tanggal' => $trPakai->tanggal,
            'Pakai Ball' => $trPakai->pakai_ball,
            'Isi Perball' => $trPakai->isiPerball->name ?? null,  // Ambil nama dari relasi
            'Pakai Pcs' => $trPakai->pakai_pcs,
            'Reject' => $trPakai->reject,
            'Jumlah Pakai' => $trPakai->jumlah_pakai,
            'Kategori' => $trPakai->category->name ?? null,  // Ambil nama kategori dari relasi
            'Mesin' => $trPakai->mesin->name ?? null,  // Ambil nama mesin dari relasi
            'Note' => $trPakai->note ?? "-",
        ];

        // Update data transaksi pada model yang ditemukan
        $trPakai->update([
            'tanggal' => $tanggal,
            'pakai_ball' => $pakaiBall,
            'isi_perball_id' => $validated['isi_perball_id'],
            'pakai_pcs' => $pakaiPcs,
            'jumlah_pakai' => $jumlahPakai,
            'category_id' => $validated['category_id'],
            'reject' => $reject,
            'mesin_id' => $validated['mesin_id'],
            'note' => $validated['note'],
        ]);

        // Ambil nama untuk data baru
        $dataNew = [
            'Tanggal' => $tanggal,
            'Pakai Ball' => $pakaiBall,
            'Isi Perball' => $isiPerball->name,  // Ambil nama dari relasi
            'Pakai Pcs' => $pakaiPcs,
            'Reject' => $reject,
            'Jumlah Pakai' => $jumlahPakai,
            'Kategori' => Category::find($validated['category_id'])->name ?? null,  // Ambil nama kategori dari relasi
            'Mesin' => Mesin::find($validated['mesin_id'])->name ?? null,  // Ambil nama mesin dari relasi
            'Note' => $validated['note'] ?? '-',
        ];

        // Catat aktivitas update
        LogService::logActivity('Update', 'Transaksi Pakai', 'Mengupdate data transaksi Pakai baru.', [
            'Data Lama' => json_encode($dataOld, JSON_PRETTY_PRINT),
            'Data Baru' => json_encode($dataNew, JSON_PRETTY_PRINT),
        ]);

        // Redirect ke halaman transaksi dengan pesan sukses
        return redirect('/tr_pakai')->with('success', 'Transaksi berhasil diperbarui.');
    }


    public function delete($id)
    {
        // Ambil transaksi yang akan dihapus
        $trPakai = TrPakai::findOrFail($id);

        // Hapus transaksi
        $trPakai->delete();

        LogService::logActivity('Delete', 'Transaksi Pakai', 'Menghapus transaksi pakai.', [
            'Tanggal' => $trPakai->tanggal,
            'Pakai Ball' => $trPakai->pakai_ball,
            'Isi Perball' => $trPakai->isi_perball_id,
            'Pakai Pcs' => $trPakai->pakai_pcs,
            'Reject' => $trPakai->reject,
            'Jumlah Pakai (Pcs)' => $trPakai->jumlah_pakai,
            'Kategori' => $trPakai->category_id,
            'Mesin' => $trPakai->mesin_id,
            'Note' => $trPakai->note,
        ]);

        return redirect('/tr_pakai');
    }
}
