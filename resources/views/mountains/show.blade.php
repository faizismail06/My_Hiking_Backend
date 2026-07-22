@extends('layouts.admin-modern')

@section('page-title', 'Detail Gunung')
@section('page-subtitle', 'Informasi lengkap tentang gunung')

@section('main-content')
<div class="row g-4">
    <!-- Image Card -->
    <div class="col-lg-4">
        <div class="modern-card animate-fade-in">
            <div class="card-body p-0">
                @if ($mountain->gambar_gunung)
                    <img src="{{ asset('/storage/images/' . $mountain->gambar_gunung) }}" class="w-100" style="border-radius: 12px; aspect-ratio: 1; object-fit: cover;" alt="{{ $mountain->nama }}">
                @else
                    <div class="d-flex align-items-center justify-content-center" style="aspect-ratio: 1; background: #f1f5f9; border-radius: 12px;">
                        <div class="text-center text-muted">
                            <i class="fas fa-mountain fa-3x mb-2"></i>
                            <p class="mb-0">Tidak ada gambar</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Detail Card -->
    <div class="col-lg-8">
        <div class="modern-card animate-fade-in" style="animation-delay: 0.1s">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-info-circle"></i> Informasi Gunung</h5>
                <span class="modern-badge badge-info">{{ $mountain->ketinggian }} mdpl</span>
            </div>
            <div class="card-body">
                <h3 class="mb-4">{{ $mountain->nama }}</h3>
                
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box me-3" style="width: 40px; height: 40px; background: rgba(79, 70, 229, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-map text-primary"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Provinsi</small>
                                <span class="fw-semibold">{{ $mountain->province->name ?? 'Tidak Diketahui' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="text-muted mb-3"><i class="fas fa-align-left me-2"></i>Deskripsi</h6>
                <p class="text-muted">{{ $mountain->deskripsi ?? 'Tidak ada deskripsi tersedia.' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('mountains.index') }}" class="btn btn-modern btn-outline-modern">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
    </a>
    <a href="{{ route('mountains.edit', $mountain->id) }}" class="btn btn-modern btn-warning-modern">
        <i class="fas fa-edit"></i> Edit Gunung
    </a>
</div>
@endsection
