<?php

namespace App\Http\Controllers\Admin;

use App\Models\Mesin;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MesinController extends Controller
{
    public function index()
    {
        $mesin = Mesin::all();
        LogService::logActivity('Lihat Data', 'Mesin', 'Melihat daftar Mesin', [
            'Jumlah Mesin Yang Ditampilkan' => $mesin->count(),
        ]);
        return view('pages.mesin.index', compact('mesin'));
    }

    public function create()
    {
        return view('pages.mesin.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate(
            [
                "name" => "required|unique:mesin,name",
            ],
            [
                "name,required" => "Nama Mesin Harus Diisi",
                "name,unique" => "Nama Mesin sudah ada!",
            ]
        );
        $mesin = new Mesin();
        $mesin->name = $request->input('name');
        $mesin->save();

        LogService::logActivity('Create', 'Mesin', 'Menambahkan data Mesin', [
            'Nama' => $mesin->name,
        ]);

        return redirect('/mesin');
    }

    public function edit($id)
    {
        $mesin = Mesin::find($id);
        return view('pages.mesin.edit', compact('mesin'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate(
            [
                "name" => "required|unique:mesin,name",
            ],
            [
                "name,required" => "Nama Mesin Harus Diisi",
                "name,unique" => "Nama Mesin sudah ada!",
            ]
        );
        $mesin = Mesin::find($id);
        // Catat data sebelum update (hanya nama mesin)
        $dataOld = ['Nama Mesin' => $mesin->name];

        $mesin->name = $request->input('name');
        $mesin->save();

        $dataNew = ['Nama Mesin' => $mesin->name];

        LogService::logActivity('Update', 'Mesin', 'Mengupdate data Mesin', [
            'Data Lama' => json_encode($dataOld, JSON_PRETTY_PRINT),
            'Data Baru' => json_encode($dataNew, JSON_PRETTY_PRINT),
        ]);



        return redirect('/mesin');
    }

    public function delete($id)
    {
        // Mencari data berdasarkan ID
        $delete = Mesin::findOrFail($id);

        // Menghapus data yang ditemukan
        $delete->delete();

        LogService::logActivity('Delete', 'Mesin', 'Menghapus data Mesin', [
            'Nama Mesin Yang Dihapus' => $delete->name,
        ]);

        return redirect('/mesin');
    }
}
