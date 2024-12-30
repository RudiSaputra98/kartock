@extends('layouts.main')

@section('header')

<div class="row mb-2">
    <div class="col-sm-6">
      <h1>Ubah Produk</h1>
    </div>
    
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active">Produk</li>
        </ol>
      </div>
  </div>

@endsection

@section('content')

<div class="row">
  <div class="col">
    <form action="/products/{{ $product->id }}" method="POST">
        @csrf
        @method('PUT')
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label for="name" class="form-label">Nama Produk</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}">
                @error('name')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea 
                name="description" 
                id="description "
                cols="30" 
                rows="10" 
                class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>

            </div>

            <div class="form-group">
                <label for="sku" class="form-label">SKU Produk</label>
                <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}">

                @error('sku')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror

            </div>
           
            <div class="form-group">
              <label for="mesin_id" class="form-label">Mesin</label>
              <select name="mesin_id" id="mesin_id" class="form-control @error('mesin_id') is-invalid @enderror">4

                  @foreach ($mesin as $mesin)
                  <option value="{{ $mesin ->id }}" {{ $product->mesin_id === $mesin->id ? 'selected' :'' }}>{{ $mesin->name }}</option>                        
                  @endforeach
              </select>

              @error('mesin_id')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
          </div>

            <div class="form-group">
                <label for="stock" class="form-label">Stok Produk</label>
                <input type="number" inputmode="numeric" name="stock" id="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $product->stock) }}">

                @error('stock')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="category_id" class="form-label">Kategori Produk</label>
                <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror">

                    @foreach ($categories as $category)
                    <option value="{{ $category ->id }}" {{ $product->category_id === $category->id ? 'selected' :'' }}>{{ $category->name }}</option>                        
                    @endforeach
                </select>

                @error('category_id')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

  
          </div>
        </div>

    <div class="card-footer">
        <div class="d-flex justify-content-end">
            <a href="/products" class="btn btn-sm btn-outline-secondary mr-3">Batal</a>
            <button type="submit" class="btn btn-sm btn-warning">Simpan</button>
        </div>
    </div>
      </div>
    
    </form>

  </div>

@endsection