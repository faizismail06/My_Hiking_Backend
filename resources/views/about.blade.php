@extends('layouts.admin-modern')

@section('page-title', 'Tentang')
@section('page-subtitle', 'Informasi tentang aplikasi My Hiking')

@section('main-content')
<div class="row g-4">
    <div class="col-lg-8 mx-auto">
        <div class="modern-card animate-fade-in">
            <div class="card-body text-center py-5">
                <img src="{{ asset('img/favicon.png') }}" class="rounded-circle mb-4" alt="MyHiking Logo" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid var(--primary-light);">
                <h3 class="fw-bold mb-2">MyHiking Admin</h3>
                <p class="text-muted mb-4">Sistem Manajemen Pendakian Gunung</p>
                
                <div class="d-flex justify-content-center gap-4 mb-4">
                    <div class="text-center">
                        <div class="stat-number fs-4 fw-bold text-primary">1.0</div>
                        <small class="text-muted">Versi</small>
                    </div>
                    <div class="text-center">
                        <div class="stat-number fs-4 fw-bold text-success">Laravel</div>
                        <small class="text-muted">Framework</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="modern-card mt-4 animate-fade-in" style="animation-delay: 0.1s">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Tentang Aplikasi</h5>
            </div>
            <div class="card-body">
                <p>MyHiking adalah aplikasi sistem manajemen pendakian gunung yang memudahkan pengelolaan:</p>
                <ul class="list-unstyled mt-3">
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Data gunung dan jalur pendakian</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Pemesanan dan transaksi pendakian</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Manajemen pengguna dan penjaga jalur</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Riwayat pendakian dengan QR scanner</li>
                </ul>
            </div>
        </div>

        <div class="modern-card mt-4 animate-fade-in" style="animation-delay: 0.2s">
            <div class="card-header">
                <h5><i class="fas fa-code"></i> Credits</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">MyHiking Admin menggunakan beberapa teknologi open-source:</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                            <i class="fab fa-laravel fa-2x text-danger"></i>
                            <div>
                                <h6 class="mb-0">Laravel</h6>
                                <small class="text-muted">PHP Framework</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                            <i class="fab fa-bootstrap fa-2x text-primary"></i>
                            <div>
                                <h6 class="mb-0">Bootstrap 5</h6>
                                <small class="text-muted">CSS Framework</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@endsection
