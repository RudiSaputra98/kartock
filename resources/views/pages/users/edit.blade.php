@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Ubah User</h1>
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
    <div class="row">
        <div class="col">
            <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <!-- Nama User -->
                        <div class="form-group">
                            <label for="name" class="form-label">Nama User</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}" required placeholder="Masukkan nama user">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Email User -->
                        <div class="form-group">
                            <label for="email" class="form-label">Email User</label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}" required placeholder="Masukkan email user">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Password Baru -->
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Masukkan password baru">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Foto Profil -->
                        <div class="form-group">
                            <label for="photo" class="form-label">Foto Profil</label>
                            @if ($user->photo)
                                <!-- Mengecek apakah user memiliki foto -->
                                <div class="mb-2">
                                    <img src="{{ asset('storage/images/' . $user->photo) }}" alt="Foto Profil"
                                        class="img-thumbnail" style="width: 100px; height: 100px;">
                                    <small class="d-block mt-1">Foto Profil Saat Ini</small>
                                </div>
                            @endif
                            <input type="file" name="photo" id="photo"
                                class="form-control @error('photo') is-invalid @enderror">
                            @error('photo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary mr-3">Batal</a>
                        <button type="submit" class="btn btn-sm btn-warning">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
