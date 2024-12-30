@extends('layouts.main')

@section('header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Ubah Rincian Transaksi Pemakaian Karung</h1>
        </div>

        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active">Edit Transaksi Pemakaian</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col">
            <form action="/tr_pakai/{{ $tr_pakai->id }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal"
                                class="form-control @error('tanggal') is-invalid @enderror"
                                value="{{ old('tanggal', $tr_pakai->tanggal) }}">
                            @error('tanggal')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="pakai_ball" class="form-label">Pakai Karung (Ball)</label>
                            <input type="text" name="pakai_ball" id="pakai_ball"
                                class="form-control @error('pakai_ball') is-invalid @enderror"
                                value="{{ old('pakai_ball', $tr_pakai->pakai_ball) }}">

                            @error('pakai_ball')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="isi_perball_id" class="form-label">Isi Perball</label>
                            <select name="isi_perball_id" id="isi_perball_id"
                                class="form-control @error('isi_perball_id') is-invalid @enderror">

                                @foreach ($isiPerball as $isiPerball)
                                    <option
                                        value="{{ $isiPerball->id }}"{{ $tr_pakai->isi_perball_id === $isiPerball->id ? 'selected' : '' }}>
                                        {{ $isiPerball->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('isi_perball_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group">
                            <label for="pakai_pcs" class="form-label">Karung masuk (Pcs)</label>
                            <input type="text" name="pakai_pcs" id="pakai_pcs"
                                class="form-control @error('pakai_pcs') is-invalid @enderror"
                                value="{{ old('pakai_pcs', $tr_pakai->pakai_pcs) }}">

                            @error('pakai_pcs')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reject" class="form-label">Reject (Pcs)</label>
                            <input type="text" name="reject" id="reject"
                                class="form-control @error('reject') is-invalid @enderror"
                                value="{{ old('reject', $tr_pakai->reject) }}">

                            @error('reject')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group">
                            <label for="category_id" class="form-label">Kategori karung</label>
                            <select name="category_id" id="category_id"
                                class="form-control @error('category_id') is-invalid @enderror">

                                @foreach ($categories as $category)
                                    <option
                                        value="{{ $category->id }}"{{ $tr_pakai->category_id === $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('category_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group">
                            <label for="mesin_id" class="form-label">Kategori karung</label>
                            <select name="mesin_id" id="mesin_id"
                                class="form-control @error('mesin_id') is-invalid @enderror">

                                @foreach ($mesin as $mesin)
                                    <option
                                        value="{{ $mesin->id }}"{{ $tr_pakai->mesin_id === $mesin->id ? 'selected' : '' }}>
                                        {{ $mesin->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('mesin_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="note" class="form-label">Catatan</label>
                            <textarea name="note" id="note " cols="30" rows="3"
                                class="form-control @error('note') is-invalid @enderror">{{ old('note', $tr_pakai->note) }}</textarea>

                        </div>


                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <a href="/tr_pakai" class="btn btn-sm btn-outline-secondary mr-3">Batal</a>
                        <button type="submit" class="btn btn-sm btn-warning">Simpan</button>
                    </div>
                </div>
        </div>

        </form>

    </div>
@endsection
