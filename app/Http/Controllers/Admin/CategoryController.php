<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        LogService::logActivity('Lihat Data', 'Kategori', 'Melihat data kategori', [
            'jumlah kategori yang dilihat' => $categories->count(),
        ]);

        return view('pages.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('pages.categories.create');
    }

    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate(
    //         [
    //             'max_stok' => 'required|integer',
    //             'warning_stok' => 'required|integer',
    //             'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
    //             'note' => 'nullable|string|max:255',
    //             "name" => "required|unique:categories,name",
    //         ],
    //         [
    //             'name.required' => 'Nama Kategori Harus Diisi',
    //             'name.unique' => 'Nama Kategori sudah ada!',
    //         ]
    //     );
    //     $category = new Category();
    //     $category->name = $request->input('name');
    //     $category->max_stok = $request->max_stok;
    //     $category->warning_stok = $request->warning_stok;
    //     $category->note = $request->note;

    //     // Jika ada foto yang diunggah
    //     if ($request->hasFile('photo')) {
    //         $photoPath = $request->file('photo')->store('images', 'public'); // Pastikan menggunakan disk 'public'
    //         $category->photo = basename($photoPath); // Simpan nama file foto
    //     }
    //     $category->save();

    //     LogService::logActivity('Create', 'Kategori', 'Menambahkan data Kategori', [
    //         'Nama Kategori' => $category->name,
    //         'Max Stok' => $category->max_stok,
    //         'Warning Stok' => $category->warning_stok,
    //         'Note' => $category->note,
    //     ]);

    //     return redirect('/categories');
    // }

    public function store(Request $request)
    {
        $validatedData = $request->validate(
            [
                'max_stok' => 'required|integer',
                'warning_stok' => 'required|integer',
                'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048', // Pastikan ini benar
                'note' => 'nullable|string|max:255',
                'name' => 'required|unique:categories,name',
            ],
            [
                "name.required" => "Nama Kategori Harus Diisi",
                "name.unique" => "Nama Kategori sudah ada!",
            ]
        );

        // Membuat kategori baru
        $category = new Category();
        $category->name = $request->input('name');
        $category->max_stok = $request->max_stok;
        $category->warning_stok = $request->warning_stok;
        $category->note = $request->note;

        // Jika ada foto yang diunggah
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('images', 'public'); // Menyimpan foto di disk 'public'
            $category->photo = basename($photoPath); // Simpan nama file foto
        }

        // Simpan kategori ke database
        $category->save();

        // Log aktivitas
        LogService::logActivity('Create', 'Kategori', 'Menambahkan data Kategori', [
            'Nama Kategori' => $category->name,
            'Max Stok' => $category->max_stok,
            'Warning Stok' => $category->warning_stok,
            'Note' => $category->note,
        ]);

        // Redirect ke halaman kategori
        return redirect('/categories');
    }


    public function edit($id)
    {
        $category = Category::find($id);
        return view('pages.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate(
            [
                "name" => "required|unique:categories,name," . $id,
                'max_stok' => 'required|integer',
                'warning_stok' => 'required|integer',
                'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
                'note' => 'nullable|string|max:255',
                // Validasi untuk foto
            ],
            [
                "name.required" => "Nama Kategori Harus Diisi",
                "name.unique" => "Nama Kategori sudah ada!",

            ]
        );
        $category = Category::find($id);
        $dataOld = [
            'Nama Kategori' => $category->name,
            'Max Stok' => $category->max_stok,
            'Warning Stok' => $category->warning_stok,
            'Note' => $category->note,
        ];

        $category->name = $request->input('name');
        $category->max_stok = $request->max_stok;
        $category->warning_stok = $request->warning_stok;
        $category->note = $request->note;

        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($category->photo) {
                $oldPhotoPath = 'public/images/' . $category->photo;
                if (Storage::exists($oldPhotoPath)) {
                    Storage::delete($oldPhotoPath);
                }
            }

            // Unggah foto baru
            $photoPath = $request->file('photo')->store('images', 'public');
            $category->photo = basename($photoPath); // Simpan nama file foto baru
        }

        $category->save();

        $dataNew = [
            'Nama Kategori' => $category->name,
            'Max Stok' => $category->max_stok,
            'Warning Stok' => $category->warning_stok,
            'Note' => $category->note,
        ];

        LogService::logActivity('Update', 'Kategori', 'Mengupdate data Kategori', [
            'Data Lama' => json_encode($dataOld, JSON_PRETTY_PRINT),
            'Data Baru' => json_encode($dataNew, JSON_PRETTY_PRINT),
        ]);

        return redirect('/categories');
    }


    public function delete($id)
    {
        // Mencari data berdasarkan ID
        $delete = Category::findOrFail($id);

        // Menghapus data yang ditemukan
        $delete->delete();

        // Log aktivitas
        LogService::logActivity('Delete', 'Kategori', 'Menghapus data Kategori', [
            'nama ' => $delete->name,
        ]);

        // Redirect kembali ke halaman /isi_perball setelah penghapusan
        return redirect('/categories');
    }
}
