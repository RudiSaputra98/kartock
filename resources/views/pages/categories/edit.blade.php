@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Ubah Kategori Produk</h1>
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
            {{-- @if ($errors->any())
         @dd ($errors->all())
        
    @endif --}}
            <form action="/categories/{{ $category->id }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name" class="form-label">Nama Kategori Produk</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $category->name) }}">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="max_stok" class="form-label">Batas Aman Stok</label>
                            <input type="number" name="max_stok" id="max_stok"
                                class="form-control @error('max_stok') is-invalid @enderror"
                                value="{{ old('max_stok', $category->max_stok) }}">
                            @error('max_stok')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="warning_stok" class="form-label">Batas Warning Stok</label>
                            <input type="number" name="warning_stok" id="warning_stok"
                                class="form-control @error('warning') is-invalid @enderror"
                                value="{{ old('warning_stok', $category->warning_stok) }}">
                            @error('warning_stok')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="note" class="form-label">Note</label>
                            <input type="text" name="note" id="note"
                                class="form-control @error('note') is-invalid @enderror"
                                value="{{ old('note', $category->note) }}">
                            @error('note')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="photo" class="form-label">Foto Karung</label>
                            @if ($category->photo)
                                <!-- Mengecek apakah user memiliki foto -->
                                <div class="mb-2">
                                    <img src="{{ asset('storage/images/' . $category->photo) }}" alt="Foto Karung"
                                        class="img-thumbnail" style="width: 100px; height: 100px;">
                                    <small class="d-block mt-1">Foto Karung</small>
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
                        <a href="/categories" class="btn btn-sm btn-outline-secondary mr-3">Batal</a>
                        <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                    </div>
                </div>
        </div>

        </form>

    </div>
@endsection
