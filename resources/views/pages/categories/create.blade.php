@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Tambah Kategori Produk</h1>
        </div>

        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Kategori</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col">
            <form action="/categories/store" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')

                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name" class="form-label">Nama Kategori Produk</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="max_stok" class="form-label">Batas Aman Stok</label>
                            <input type="number" name="max_stok" id="max_stok"
                                class="form-control @error('max_stok') is-invalid @enderror" value="{{ old('max_stok') }}">
                            @error('max_stok')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="warning_stok" class="form-label">Batas Warning Stok</label>
                            <input type="number" name="warning_stok" id="warning_stok"
                                class="form-control @error('warning_stok') is-invalid @enderror"
                                value="{{ old('warning_stok') }}">
                            @error('warning_stok')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="note" class="form-label">Note</label>
                            <input type="text" name="note" id="note"
                                class="form-control @error('note') is-invalid @enderror" value="{{ old('note') }}">
                            @error('note')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" name="photo" id="photo"
                                class="form-control @error('photo') is-invalid @enderror" value="{{ old('photo') }}">
                            @error('photo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <a href="/categories" class="btn btn-sm btn-outline-secondary mr-3">Batal</a>
                        <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                    </div>
                </div>
        </div>

        </form>

    </div>
@endsection
