@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Laporan Transaksi In Out</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Report</li>
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
                min-width: 500px;
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


                <!-- Filter -->
                <div class="card-header">
                    <form method="GET" action="{{ route('report.index') }}">
                        <div class="row justify-content-center">
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
                            <div class="col-md-4 mb-2 text-center">
                                <label for="category" class="d-block">Kategori</label>
                                <select id="category" name="category" class="form-control text-center">
                                    <option value="">-- Semua Kategori --</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>


                            </div>
                        </div>


                        <div class="row mt-3">
                            <div class="col text-center">
                                <button type="submit" class="btn btn-primary mx-2">Filter</button>

                                <a href="{{ route('report.index') }}" class="btn btn-secondary mx-2">Reset</a>


                                <button class="btn btn-success dropdown-toggle" type="button" id="dropdownCetak"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Cetak Laporan
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownCetak">
                                    <a class="dropdown-item"
                                        href="{{ route('pdf.report', [
                                            'start_date' => request('start_date'),
                                            'end_date' => request('end_date'),
                                            'category' => request('category'),
                                        ]) }}"
                                        target="_blank">Cetak PDF</a>

                                    <a class="dropdown-item"
                                        href="{{ route('excell.report', [
                                            'start_date' => request('start_date'),
                                            'end_date' => request('end_date'),
                                            'category' => request('category'),
                                        ]) }}"
                                        target="_blank">Export ke Excel</a>
                                </div>

                            </div>

                        </div>

                    </form>
                </div>

                <!-- Data -->
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th class="sortable">Tanggal</th>
                                    <th>Masuk (Pcs)</th>
                                    <th>Keluar (Pcs)</th>
                                    <th class="sortable">Kategori</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($paginatedData as $data)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        {{-- <td>{{ $data['tanggal'] ?? '-' }}</td> --}}
                                        <td>{{ \Carbon\Carbon::parse($data['tanggal'])->format('d/m/Y') }}</td>
                                        {{-- <td>{{ $data['tr_masuk'] == 0 ? '-' : $data['tr_masuk'] }}</td>
                                    <td>{{ $data['tr_pakai'] == 0 ? '-' : $data['tr_pakai'] }}</td>

                                    <td>{{ $data['category'] ?? 'Tidak Diketahui' }}</td>
                                    <td>{{ $data['stok'] == 0 ? '-' : $data['stok'] }}</td> --}}



                                        <td class="tr-masuk">
                                            {{ $data['tr_masuk'] ? number_format($data['tr_masuk'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="tr-pakai">
                                            {{ $data['tr_pakai'] ? number_format($data['tr_pakai'], 0, ',', '.') : '-' }}
                                        </td>

                                        <td>{{ $data['category'] ?? 'Tidak Diketahui' }}</td>
                                        <td class="stok">
                                            {{ $data['stok'] ? number_format($data['stok'], 0, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data untuk periode ini.</td>
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
                                        {{ $paginatedData->appends(request()->query())->links('pagination::bootstrap-4') }}
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
