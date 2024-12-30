@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Tambah User</h1>
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
            <form action="/users/store" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')
                <div class="card">
                    <div class="card-body">
                        <!-- Nama User -->
                        <div class="form-group">
                            <label for="name" class="form-label">Nama User</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                required placeholder="Masukkan nama user">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Email User -->
                        <div class="form-group">
                            <label for="email" class="form-label">Email User</label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                required placeholder="Masukkan email user">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror" value="{{ old('password') }}"
                                required placeholder="Masukkan password">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Foto Profil -->
                        <div class="form-group">
                            <label for="photo" class="form-label">Foto Profil</label>
                            <input type="file" name="photo" id="photo" class="form-control">
                        </div>

                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <a href="/users" class="btn btn-sm btn-outline-secondary mr-3">Batal</a>
                        <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
