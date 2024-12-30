@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>TAMBAH USER</h1>
        </div>

        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">User</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col text-right">
            <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus-circle"></i> Tambah Pengguna
            </a>
        </div>
    </div>

    <div class="row">
        @foreach ($users as $user)
            <div class="col-12 col-sm-6 col-md-4 d-flex align-items-stretch flex-column">
                <div class="card bg-light d-flex flex-fill">
                    <div class="card-header text-muted border-bottom-0">
                        Admin
                    </div>
                    <div class="card-body pt-0">
                        <div class="row">
                            <div class="col-7">
                                <h2 class="lead"><b>{{ $user->name }}</b></h2>
                                <p class="text-muted text-sm"><b>Email: </b> {{ $user->email ?? 'No description' }}</p>
                                <p class="text-muted text-sm"><b>Password: </b> Tersimpan</p>
                            </div>
                            <div class="col-5 text-center">
                                @if ($user->photo)
                                    <img src="{{ asset('storage/images/' . $user->photo) }}" alt="user-avatar"
                                        class="img-circle img-fluid">
                                @else
                                    <img src="{{ asset('public/img/Avatar.jpg') }}" alt="user-avatar"
                                        class="img-circle img-fluid">
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between w-100">
                            <!-- Tombol View Profile di kiri -->
                            <a href="#" class="btn btn-sm btn-primary">
                                <i class="fas fa-user"></i> View Profile
                            </a>

                            <!-- Tombol Ubah di tengah (gunakan mx-auto untuk tengah) -->
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning mx-auto">
                                <i class="fas fa-pen"></i> Ubah
                            </a>

                            <form action="{{ route('users.delete', $user->id) }}" method="POST" style="display: inline;"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                @csrf
                                @method('DELETE') <!-- Ini untuk mengubah metode POST menjadi DELETE -->
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>


                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
