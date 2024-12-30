@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>KATEGORI PRODUK</h1>
        </div>

        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Kategori Produk</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <style>
        @media (max-width: 767px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                min-width: 1000px;
                /* Lebar minimum tabel */
                width: 100%;
                table-layout: auto;
                /* Kolom menyesuaikan ukuran */
            }

            table th,
            table td {
                white-space: normal;
                /* Agar teks membungkus */
                word-wrap: break-word;
                /* Membungkus teks panjang */
                font-size: 14px;
                /* Ukuran font lebih kecil untuk mobile */
                padding: 6px;
                /* Padding lebih kecil */
            }

            /* Penyesuaian tombol */
            .btn {
                font-size: 12px;
                padding: 6px 8px;
            }

            /* Penyesuaian dropdown menu */
            .dropdown-menu {
                min-width: 140px;
            }
        }
    </style>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header d-flex justify-content-end">
                    <a href="/categories/create" class="btn btn-sm btn-primary">
                        Tambah Kategori Karung
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Batas Aman</th>
                                    <th>Warning</th>
                                    <th>Note</th>
                                    <th>Photo</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>
                                            {{ $category['max_stok'] ? number_format($category['max_stok'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td>
                                            {{ $category['warning_stok'] ? number_format($category['warning_stok'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td>{{ $category->note ?? '-' }}</td>
                                        <td>
                                            @if ($category->photo)
                                                <img src="{{ asset('storage/images/' . $category->photo) }}"
                                                    alt="category-avatar" class="img-circle img-fluid" style="height: 50px">
                                            @else
                                                <img src="{{ asset('img/Avatar.jpg') }}" alt="category-avatar"
                                                    class="img-circle img-fluid" style="height: 50px">
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <!-- Tombol Ubah -->
                                                <a href="/categories/edit/{{ $category->id }}"
                                                    class="btn btn-sm btn-warning mr-2">Ubah</a>

                                                <!-- Form Hapus dengan Konfirmasi -->
                                                <form action="/categories/{{ $category->id }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
