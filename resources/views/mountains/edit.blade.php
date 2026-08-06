@extends('layouts.admin-modern')

@section('page-title', 'Edit Gunung')
@section('page-subtitle', 'Perbarui data gunung')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header">
        <h5><i class="fas fa-edit"></i> Form Edit Gunung</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('mountains.update', $mountain->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Gunung <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ old('nama', $mountain->nama) }}" placeholder="Masukkan nama gunung">
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ketinggian (mdpl) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('ketinggian') is-invalid @enderror" name="ketinggian" value="{{ old('ketinggian', $mountain->ketinggian) }}" placeholder="Masukkan ketinggian">
                    @error('ketinggian')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="province_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                    <select id="province_id" name="province_id" class="form-select @error('province_id') is-invalid @enderror">
                        <option value="" disabled>Pilih Provinsi</option>
                        @foreach($province_id as $province)
                            <option value="{{ $province->id }}" {{ old('province_id', $mountain->province_id) == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                        @endforeach
                    </select>
                    @error('province_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" name="deskripsi" rows="4" placeholder="Masukkan deskripsi gunung">{{ old('deskripsi', $mountain->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Gambar Gunung</label>
                    <input type="file" class="form-control @error('gambar_gunung') is-invalid @enderror" name="gambar_gunung" accept="image/*">
                    @error('gambar_gunung')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if (!empty($mountain->gambar_gunung))
                        <div class="mt-2">
                            <img src="{{ asset('/storage/images/' . $mountain->gambar_gunung) }}" alt="Gambar Gunung" class="rounded" style="max-width: 200px;">
                        </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label">Latitude</label>
                    <input type="text" class="form-control @error('latitude') is-invalid @enderror" name="latitude" value="{{ old('latitude', $mountain->latitude) }}" placeholder="-7.2575">
                    @error('latitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Longitude</label>
                    <input type="text" class="form-control @error('longitude') is-invalid @enderror" name="longitude" value="{{ old('longitude', $mountain->longitude) }}" placeholder="112.7521">
                    @error('longitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('mountains.index') }}" class="btn btn-modern btn-outline-modern">Batal</a>
                <button type="submit" class="btn btn-modern btn-primary-modern">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
