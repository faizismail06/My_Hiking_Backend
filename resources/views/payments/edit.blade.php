@extends('layouts.admin-modern')

@section('page-title', 'Edit Pembayaran')
@section('page-subtitle', 'Perbarui metode pembayaran')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header">
        <h5><i class="fas fa-edit"></i> Form Edit Pembayaran</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('payments.update', $payment->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">ID Pembayaran</label>
                    <input type="text" class="form-control" value="{{ $payment->id }}" readonly disabled>
                    <small class="text-muted">ID tidak dapat diubah</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Pembayaran <span class="text-danger">*</span></label>
                    <input type="text" name="nama_pembayaran" class="form-control @error('nama_pembayaran') is-invalid @enderror" value="{{ old('nama_pembayaran', $payment->nama_pembayaran) }}" placeholder="Contoh: Bank BCA">
                    @error('nama_pembayaran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Pembayaran <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_pembayaran" class="form-control @error('nomor_pembayaran') is-invalid @enderror" value="{{ old('nomor_pembayaran', $payment->nomor_pembayaran) }}" placeholder="Contoh: 1234567890">
                    @error('nomor_pembayaran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Logo Pembayaran</label>
                    <input type="file" class="form-control @error('gambar_pembayaran') is-invalid @enderror" name="gambar_pembayaran" accept="image/*">
                    @error('gambar_pembayaran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if ($payment->gambar_pembayaran)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $payment->gambar_pembayaran) }}" alt="Logo" class="rounded" style="max-width: 100px;">
                            <p class="small text-muted mb-0">Logo saat ini</p>
                        </div>
                    @endif
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
