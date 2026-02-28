@extends('layouts.admin-modern')

@section('page-title', 'Tambah Pembayaran')
@section('page-subtitle', 'Tambahkan metode pembayaran baru')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header">
        <h5><i class="fas fa-credit-card"></i> Form Metode Pembayaran</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">ID Pembayaran <span class="text-danger">*</span></label>
                    <input type="text" name="id" class="form-control @error('id') is-invalid @enderror" value="{{ old('id') }}" placeholder="Contoh: BCA, MANDIRI, OVO">
                    @error('id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Pembayaran <span class="text-danger">*</span></label>
                    <input type="text" name="nama_pembayaran" class="form-control @error('nama_pembayaran') is-invalid @enderror" value="{{ old('nama_pembayaran') }}" placeholder="Contoh: Bank BCA">
                    @error('nama_pembayaran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Pembayaran <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_pembayaran" class="form-control @error('nomor_pembayaran') is-invalid @enderror" value="{{ old('nomor_pembayaran') }}" placeholder="Contoh: 1234567890">
                    @error('nomor_pembayaran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Logo Pembayaran</label>
                    <input type="file" class="form-control @error('gambar_pembayaran') is-invalid @enderror" id="gambar_pembayaran" name="gambar_pembayaran" accept="image/*">
                    @error('gambar_pembayaran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('payments.index') }}" class="btn btn-modern btn-outline-modern">Batal</a>
                <button type="submit" class="btn btn-modern btn-primary-modern">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
