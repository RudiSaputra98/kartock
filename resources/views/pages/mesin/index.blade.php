@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Mesin</h1>
        </div>

        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Mesin</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header d-flex justify-content-end">
                    <a href="/mesin/create" class="btn btn-sm btn-primary">
                        Tambah Mesin
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mesin as $mesin)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $mesin->name }}</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="/mesin/edit/{{ $mesin->id }}"
                                                class="btn btn-sm btn-warning mr-2">Ubah</a>

                                            <form action="/mesin/{{ $mesin->id }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    Hapus
                                                </button>
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
@endsection
