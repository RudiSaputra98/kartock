<?php

namespace App\Http\Controllers\Admin;

use App\Models\IsiPerball;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IsiPerballController extends Controller
{
    public function index()
    {
        $isi_perball = IsiPerball::get();

        // LogService::logActivity('View', 'Isi Per Ball', 'Melihat data isi per ball', []);
        LogService::logActivity('Lihat Data', 'Isi Per Ball', 'Melihat data isi per ball', [
            'jumlah data yang dilihat' => $isi_perball->count(),
        ]);
        return view('pages.isi_perball.index', [
            "isi_perball" => $isi_perball,
        ]);
    }

    public function create()
    {
        $isi_perball = IsiPerball::all();
        return view(
            'pages.isi_perball.create',
            [
                "isi_perball" => $isi_perball,
            ]
        );
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            "name" => "required",
            "note" => "nullable",
        ]);

        // Membuat instance baru untuk IsiPerball
        $isi_perball = new IsiPerball();
        $isi_perball->name = $validated['name'];  // Gunakan data yang sudah divalidasi
        $isi_perball->note = $validated['note'];  // Gunakan data yang sudah divalidasi

        // Simpan data ke database
        $isi_perball->save();  // Gunakan save(), bukan create()

        // Log aktivitas
        LogService::logActivity('Create', 'Isi Per ball', 'Menambahkan data isi per ball', [
            'name' => $isi_perball->name,
            'note' => $isi_perball->note,
        ]);

        // Redirect setelah menyimpan data
        return redirect('/isi_perball');
    }



    public function edit($id)
    {
        $isi_perball = IsiPerball::findOrFail($id);

        return view('pages.isi_perball.edit', [
            "isi_perball" => $isi_perball,
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                "name" => "required|string|max:255",
                "note" => "nullable|string", // Menambahkan validasi agar note adalah string jika ada
            ]);

            // Mencari data yang akan diperbarui
            $isi_perball = IsiPerball::findOrFail($id);

            // Menyimpan data lama sebelum update
            $dataOld = [
                'name' => $isi_perball->name,
                'note' => $isi_perball->note,
            ];

            // Memeriksa apakah data yang diperbarui berbeda dari data yang sudah ada
            $isUpdated = false;
            if ($isi_perball->name !== $validated['name'] || $isi_perball->note !== $validated['note']) {
                $isi_perball->update([
                    'name' => $validated['name'],
                    'note' => $validated['note'],
                ]);
                $isUpdated = true;
            }

            // Jika data tidak diperbarui
            if (!$isUpdated) {
                return redirect('/isi_perball')->with('message', 'No changes made.');
            }

            // Menyimpan data baru setelah update
            $dataNew = [
                'name' => $isi_perball->name,
                'note' => $isi_perball->note,
            ];


            // Log aktivitas dengan format JSON
            LogService::logActivity('Update', 'Isi Perball', 'Memperbarui data isi per ball', [
                'Data Lama' => json_encode($dataOld, JSON_PRETTY_PRINT),
                'Data Baru' => json_encode($dataNew, JSON_PRETTY_PRINT),
            ]);

            // Redirect setelah memperbarui data dengan pesan sukses
            return redirect('/isi_perball')->with('message', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            // Menangani kesalahan jika ada
            return redirect('/isi_perball')->with('error', 'Terjadi kesalahan saat memperbarui data.');
        }
    }




    public function delete($id)
    {
        // Mencari data berdasarkan ID
        $delete = IsiPerball::findOrFail($id);

        // Menghapus data yang ditemukan
        $delete->delete();

        // Log aktivitas
        LogService::logActivity('Delete', 'Isi Per Ball', 'Menghapus data Isi Per Ball', [
            'name' => $delete->name,
            'note' => $delete->note,
        ]);

        // Redirect kembali ke halaman /isi_perball setelah penghapusan
        return redirect('/isi_perball');
    }
}
