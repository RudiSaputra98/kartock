@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Ubah Rincian Transaksi Karung Masuk</h1>
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
    <div class="row">
        <div class="col">
            <form action="/tr_masuk/{{ $tr_masuk->id }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal"
                                class="form-control @error('tanggal') is-invalid @enderror"
                                value="{{ old('tanggal', $tr_masuk->tanggal) }}">
                            @error('tanggal')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="masuk_ball" class="form-label">Karung masuk (Ball)</label>
                            <input type="number" name="masuk_ball" id="masuk_ball"
                                class="form-control @error('masuk_ball') is-invalid @enderror"
                                value="{{ old('masuk_ball', $tr_masuk->masuk_ball) }}">

                            @error('masuk_ball')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="isi_perball_id" class="form-label">Isi Perball</label>
                            <select name="isi_perball_id" id="isi_perball_id"
                                class="form-control @error('isi_perball_id') is-invalid @enderror">

                                @foreach ($isiPerball as $isiPerball)
                                    <option
                                        value="{{ $isiPerball->id }}"{{ $tr_masuk->isi_perball_id === $isiPerball->id ? 'selected' : '' }}>
                                        {{ $isiPerball->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('isi_perball_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group">
                            <label for="masuk_pcs" class="form-label">Karung masuk (Pcs)</label>
                            <input type="number" name="masuk_pcs" id="masuk_pcs"
                                class="form-control @error('masuk_pcs') is-invalid @enderror"
                                value="{{ old('masuk_pcs', $tr_masuk->masuk_pcs) }}">

                            @error('masuk_pcs')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="category_id" class="form-label">Kategori karung</label>
                            <select name="category_id" id="category_id"
                                class="form-control @error('category_id') is-invalid @enderror">

                                @foreach ($categories as $category)
                                    <option
                                        value="{{ $category->id }}"{{ $tr_masuk->category_id === $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('category_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="note" class="form-label">Catatan</label>
                            <textarea name="note" id="note " cols="30" rows="3"
                                class="form-control @error('note') is-invalid @enderror">{{ old('note', $tr_masuk->note) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <a href="/tr_masuk" class="btn btn-sm btn-outline-secondary mr-3">Batal</a>
                        <button type="submit" class="btn btn-sm btn-warning">Simpan</button>
                    </div>
                </div>
        </div>

        </form>

    </div>
@endsection
