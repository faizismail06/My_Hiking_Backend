@extends('layouts.admin-modern')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan dan statistik platform MyHiking')

@section('main-content')
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6 animate-fade-in">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Transaksi Masuk</p>
                        <h3 class="value mb-0">{{ $totalTransaksi }}</h3>
                    </div>
                    <div class="icon primary">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 animate-fade-in" style="animation-delay: 0.1s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Total Pendapatan</p>
                        <h3 class="value mb-0">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
                    </div>
                    <div class="icon success">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 animate-fade-in" style="animation-delay: 0.2s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Total Gunung</p>
                        <h3 class="value mb-0">{{ $totalGunung }}</h3>
                    </div>
                    <div class="icon info">
                        <i class="fas fa-mountain"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 animate-fade-in" style="animation-delay: 0.3s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Total Jalur</p>
                        <h3 class="value mb-0">{{ $totalJalur }}</h3>
                    </div>
                    <div class="icon warning">
                        <i class="fas fa-route"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-3 col-md-6 animate-fade-in" style="animation-delay: 0.4s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Total Pengguna</p>
                        <h3 class="value mb-0">{{ $totalUser }}</h3>
                    </div>
                    <div class="icon danger">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="modern-card animate-fade-in" style="animation-delay: 0.5s">
                <div class="card-header">
                    <h5><i class="fas fa-bolt"></i> Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('mountains.create') }}" class="btn btn-modern btn-primary-modern w-100">
                                <i class="fas fa-plus"></i> Tambah Gunung
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('trails.create') }}" class="btn btn-modern btn-success-modern w-100">
                                <i class="fas fa-plus"></i> Tambah Jalur
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('transactions.index') }}" class="btn btn-modern btn-warning-modern w-100">
                                <i class="fas fa-credit-card"></i> Lihat Transaksi
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('users.index') }}" class="btn btn-modern btn-outline-modern w-100">
                                <i class="fas fa-users"></i> Kelola Pengguna
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.refunds.index') }}" class="btn btn-modern btn-danger-modern w-100">
                                <i class="fas fa-rotate-left"></i> Manual Refund
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
