@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Laporan Stok Akhir</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Report Stok Akhir</li>
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
                min-width: 600px;
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
    <div class="card">
        <div class="card-header">
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
            <form method="GET" action="{{ route('report.stok.index') }}">
                <div class="row align-items-center justify-content-center mb">
                    <!-- Label Tanggal -->
                    <div class="col-auto text-center">
                        <label for="end_date" class="form-label mb-0">Per Tanggal</label>
                    </div>

                    <!-- Input Tanggal -->
                    <div class="col-md-3 col-sm-6 mb-2">
                        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                            class="form-control">
                    </div>

                    <!-- Tombol Filter, Reset, Cetak PDF -->
                    <div class="col-md-6 col-sm-12 d-flex flex-wrap justify-content-center align-items-center">
                        <button type="submit" class="btn btn-primary mx-2 mb-2">Filter</button>

                        <a href="{{ route('report.stok.index') }}" class="btn btn-secondary mx-2 mb-2">Reset</a>

                        <div class="dropdown mx-2 mb-2">
                            <button class="btn btn-success dropdown-toggle" type="button" id="dropdownCetak"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Cetak Laporan
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownCetak">
                                <a class="dropdown-item"
                                    href="{{ route('pdf.report.stok', ['end_date' => request('end_date'), 'category' => request('category')]) }}"
                                    target="_blank">Cetak PDF</a>
                                <a class="dropdown-item"
                                    href="{{ route('excell.report.stok', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'category' => request('category')]) }}"
                                    target="_blank">Cetak Excel</a>
                            </div>
                        </div>
                    </div>
                </div>








            </form>
        </div>



        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th class="sortable">Tanggal</th>
                            <th class="sortable">Kategori</th>
                            <th class="sortable">Stok</th>
                            <th class="sortable">Rincian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groupedData as $key => $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($data['tanggal'])->format('d/m/Y') }}</td>
                                <td>{{ $data['category'] }}</td>
                                <td class="stok">
                                    {{ $data['stok'] ? number_format($data['stok'], 0, ',', '.') : '-' }}
                                </td>
                                <td>{{ $data['rincian'] }}</td>


                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data tersedia</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

        </div>
    </div>
@endsection
