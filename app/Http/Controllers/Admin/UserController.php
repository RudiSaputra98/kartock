<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::get();

        // LogService::logActivity('Lihat Data', 'User', 'Melihat daftar user', []);
        LogService::logActivity('Lihat Data', 'User', 'Melihat daftar User', [
            'jumlah user yang tampil' => $users->count(),
        ]);
        return view('pages.users.index', [
            "users" => $users,
        ]);
    }

    public function create()
    {
        return view('pages.users.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|min:3",
            "email" => "required",
            "password" => "required",
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048', // validasi foto
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;

        // Jika ada foto yang diunggah
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('images', 'public'); // Pastikan menggunakan disk 'public'
            $user->photo = basename($photoPath); // Simpan nama file foto
        }


        $user->save();

        LogService::logActivity('Create', 'User', 'Menambahkan user baru', [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }


    public function edit($id)
    {
        Log::info('Edit User ID: ' . $id);  // Log ID yang diterima
        $user = User::findOrFail($id);
        Log::info('User Data: ', ['user' => $user]);  // Log data user yang diambil

        return view('pages.users.edit', compact('user'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            "name" => "required|min:3",
            "email" => "required|email",
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048', // Validasi foto
        ]);

        // Mencari user berdasarkan ID
        $user = User::findOrFail($id);

        $dataOld = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        // Memperbarui informasi user
        $user->name = $request->name;
        $user->email = $request->email;

        // Jika password diubah, kita harus melakukan enkripsi
        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        // Jika ada foto baru yang diunggah
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($user->photo) {
                $oldPhotoPath = 'public/images/' . $user->photo;
                if (Storage::exists($oldPhotoPath)) {
                    Storage::delete($oldPhotoPath);
                }
            }

            // Unggah foto baru
            $photoPath = $request->file('photo')->store('images', 'public');
            $user->photo = basename($photoPath); // Simpan nama file foto baru
        }

        // Simpan perubahan ke database
        $user->save();

        $dataNew = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        LogService::logActivity('Update', 'User', 'Merubah data user', [
            'Data Lama' => json_encode($dataOld, JSON_PRETTY_PRINT),
            'Data Baru' => json_encode($dataNew, JSON_PRETTY_PRINT),

        ]);



        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }




    public function delete($id)
    {
        $delete = User::findOrFail($id);
        // $delete = User::where('id', $id);
        $delete->delete();

        LogService::logActivity('Delete', 'User', 'Menghapus data user', [
            'name' => $delete->name,
            'email' => $delete->email,
        ]);

        return redirect('/users');
    }
}
