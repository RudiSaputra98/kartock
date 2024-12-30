@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>TRANSAKSI KARUNG MASUK</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Transaksi Masuk</li>
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
                <!-- Pesan Error jika validasi gagal -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Pesan Error jika data kosong -->
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="GET" action="{{ route('tr_masuk.index') }}">

                    <div class="row justify-content-center p-3">
                        <div class="col-md-3 mb-2 text-center">
                            <label for="start_date" class="d-block">Tanggal Mulai</label>
                            <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                                class="form-control text-center">
                        </div>
                        <div class="col-md-3 mb-2 text-center">
                            <label for="end_date" class="d-block">Tanggal Akhir</label>
                            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                                class="form-control text-center">
                        </div>
                        <div class="col-md-3 mb-2 text-center">
                            <label for="category" class="d-block">Kategori</label>
                            <select id="category" name="category" class="form-control text-center">
                                <option value="">-- Semua Kategori --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col text-center">
                            <button type="submit" class="btn btn-primary mx-2">Filter</button>
                            <a href="{{ route('tr_masuk.index') }}" class="btn btn-secondary mx-2">Reset</a>
                            <button class="btn btn-success dropdown-toggle" type="button" id="dropdownCetak"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Cetak Laporan
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownCetak">
                                <a class="dropdown-item"
                                    href="{{ route('pdf.tr_masuk', [
                                        'start_date' => request('start_date'),
                                        'end_date' => request('end_date'),
                                        'category' => request('category'),
                                    ]) }}"
                                    target="_blank">Cetak PDF</a>
                                <a class="dropdown-item"
                                    href="{{ route('excell.tr_masuk', [
                                        'start_date' => request('start_date'),
                                        'end_date' => request('end_date'),
                                        'category' => request('category'),
                                    ]) }}"
                                    target="_blank">Export ke Excel</a>
                            </div>
                            <a href="/tr_masuk/create" class="btn btn-dark mx-2">Tambah</a>
                        </div>
                    </div>
                </form>
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-bordered" style="table-layout: fixed; width: 125%;">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width: 5%;">No</th>
                                    <th class="text-center align-middle sortable" style="width: 10%;">Tanggal</th>
                                    <th class="text-center align-middle" style="width: 7%;">Masuk Ball</th>
                                    <th class="text-center align-middle" style="width: 7%;">Isi Per Ball</th>
                                    <th class="text-center align-middle" style="width: 7%;">Masuk Pcs</th>
                                    <th class="text-center align-middle" style="width: 10%;">Jumlah Masuk</th>
                                    <th class="text-center align-middle sortable" style="width: 20%;">Kategori</th>
                                    <th class="text-center align-middle" style="width: 20%;">Note</th>
                                    <th class="text-center align-middle" style="width: 14%;">#</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tr_masuk as $trmasuk)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ \Carbon\Carbon::parse($trmasuk->tanggal)->format('d/m/Y') }}</td>
                                        <td class="masuk-ball">
                                            {{ $trmasuk->masuk_ball_display > 0 ? number_format((float) $trmasuk->masuk_ball_display, 0, ',', '.') : '-' }}
                                        </td>
                                        <td>{{ $trmasuk->isi_perball_name_display > 0 ? number_format((float) $trmasuk->isi_perball_name_display, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="masuk-pcs">
                                            {{ $trmasuk->masuk_pcs_display > 0 ? number_format((float) $trmasuk->masuk_pcs_display, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="total-masuk">
                                            {{ $trmasuk->jumlah_masuk > 0 ? number_format((float) $trmasuk->jumlah_masuk, 0, ',', '.') : '-' }}
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($trmasuk->category->name, 30) }}</td>
                                        <td>
                                            {{ \Illuminate\Support\Str::limit($trmasuk->note, 40) }}</td>

                                        <td>
                                            <div class="d-flex">
                                                <a href="/tr_masuk/edit/{{ $trmasuk->id }}"
                                                    class="btn btn-sm btn-warning mr-2">Ubah</a>
                                                <form action="/tr_masuk/{{ $trmasuk->id }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data yang sesuai.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>





                    <div class="row mt-2">
                        <div class="col-sm-6">
                            <label for="entriesPerPage">Show entries</label>
                            <form method="GET" action="{{ url()->current() }}">
                                @foreach (request()->except('entriesPerPage') as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <select id="entriesPerPage" name="entriesPerPage" class="form-control w-auto"
                                    onchange="this.form.submit()">
                                    <option value="5" {{ request('entriesPerPage') == 5 ? 'selected' : '' }}>5
                                    </option>
                                    <option value="10" {{ request('entriesPerPage') == 10 ? 'selected' : '' }}>10
                                    </option>
                                    <option value="25" {{ request('entriesPerPage') == 25 ? 'selected' : '' }}>25
                                    </option>
                                    <option value="50" {{ request('entriesPerPage') == 50 ? 'selected' : '' }}>50
                                    </option>
                                    <option value="100" {{ request('entriesPerPage') == 100 ? 'selected' : '' }}>100
                                    </option>
                                </select>
                            </form>
                        </div>
                        <div class="col-sm-6">
                            <div class="float-sm-right">
                                <ul class="pagination">
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $tr_masuk->appends(request()->query())->links('pagination::bootstrap-4') }}
                                    </div>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
