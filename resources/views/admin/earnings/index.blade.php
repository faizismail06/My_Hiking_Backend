@extends('layouts.admin-modern')

@section('page-title', 'Earnings & Withdrawal')
@section('page-subtitle', 'Kelola saldo pendapatan dan request penarikan dari penjaga jalur')

@section('main-content')
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Earnings -->
        <div class="col-xl-3 col-md-6 animate-fade-in">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Total Pendapatan</p>
                        <h3 class="value mb-0">
                            Rp {{ number_format($totalEarnings ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="icon primary">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Withdrawn -->
        <div class="col-xl-3 col-md-6 animate-fade-in" style="animation-delay: 0.1s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Total Dicairkan</p>
                        <h3 class="value mb-0">
                            Rp {{ number_format($totalWithdrawn ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="icon success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="col-xl-3 col-md-6 animate-fade-in" style="animation-delay: 0.2s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Request Pending</p>
                        <h3 class="value mb-0">{{ $pendingRequests ?? 0 }}</h3>
                    </div>
                    <div class="icon warning">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approved Requests -->
        <div class="col-xl-3 col-md-6 animate-fade-in" style="animation-delay: 0.3s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Request Disetujui</p>
                        <h3 class="value mb-0">{{ $approvedRequests ?? 0 }}</h3>
                    </div>
                    <div class="icon info">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="modern-card animate-fade-in" style="animation-delay: 0.4s">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.earnings.withdrawal-requests') }}"
                            class="btn btn-modern btn-primary-modern">
                            <i class="fas fa-list-check"></i> Kelola Request Penarikan
                        </a>
                        <a href="{{ route('admin.earnings.admin-fee-settings') }}"
                            class="btn btn-modern btn-outline-modern">
                            <i class="fas fa-cog"></i> Pengaturan Biaya Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trail Guards Earnings Table -->
    <div class="row g-4">
        <div class="col-12">
            <div class="modern-card animate-fade-in" style="animation-delay: 0.5s">
                <div class="card-header">
                    <h5><i class="fas fa-table"></i> Daftar Saldo Penjaga Jalur</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Nama Penjaga</th>
                                    <th>Email / Phone</th>
                                    <th>Total Pendapatan</th>
                                    <th>Sudah Dicairkan</th>
                                    <th>Saldo Tersedia</th>
                                    <th>Transaksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trailGuards as $guard)
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div
                                                    style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary-light); display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-weight: 600; font-size: 0.85rem;">
                                                    {{ strtoupper(substr($guard->name, 0, 2)) }}
                                                </div>
                                                <span>{{ $guard->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-size: 0.85rem;">
                                                <div>{{ $guard->email }}</div>
                                                <div style="color: #64748b; font-size: 0.8rem;">{{ $guard->phone ?? '-' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>Rp
                                                {{ number_format($guard->total_earnings ?? 0, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <span style="color: var(--success-color);">Rp
                                                {{ number_format($guard->withdrawn_amount ?? 0, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge-modern"
                                                style="background: var(--primary-light); color: var(--primary-color);">
                                                Rp {{ number_format($guard->available_balance ?? 0, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-modern" style="background: #f1f5f9; color: #475569;">
                                                {{ $guard->transaction_count ?? 0 }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #64748b; padding: 2rem;">
                                            <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                            <p style="margin-top: 0.5rem;">Tidak ada data penjaga jalur</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Fee Settings Summary -->
    @if ($adminFeeSettings)
        <div class="row g-4 mt-4">
            <div class="col-12">
                <div class="modern-card animate-fade-in" style="animation-delay: 0.6s; background: var(--primary-light);">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 style="margin-bottom: 1rem; color: var(--primary-color);">
                                    <i class="fas fa-cog"></i> Pengaturan Biaya Admin Saat Ini
                                </h5>
                                <div
                                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                                    <div>
                                        <p style="margin-bottom: 0.5rem; color: #475569; font-size: 0.85rem;"><strong>Tipe
                                                Biaya:</strong></p>
                                        <span class="badge-modern" style="background: var(--primary-color); color: white;">
                                            {{ $adminFeeSettings->getFeeTypeLabel() }}
                                        </span>
                                    </div>
                                    @if ($adminFeeSettings->fee_type === 'percentage' || $adminFeeSettings->fee_type === 'both')
                                        <div>
                                            <p style="margin-bottom: 0.5rem; color: #475569; font-size: 0.85rem;">
                                                <strong>Persentase Biaya:</strong>
                                            </p>
                                            <span
                                                style="font-size: 1.25rem; font-weight: 600; color: var(--primary-color);">{{ $adminFeeSettings->fee_percentage }}%</span>
                                        </div>
                                    @endif
                                    @if ($adminFeeSettings->fee_type === 'fixed' || $adminFeeSettings->fee_type === 'both')
                                        <div>
                                            <p style="margin-bottom: 0.5rem; color: #475569; font-size: 0.85rem;">
                                                <strong>Biaya Tetap:</strong>
                                            </p>
                                            <span
                                                style="font-size: 1.25rem; font-weight: 600; color: var(--primary-color);">Rp
                                                {{ number_format($adminFeeSettings->fixed_fee, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ route('admin.earnings.admin-fee-settings') }}"
                                    class="btn btn-modern btn-primary-modern">
                                    <i class="fas fa-edit"></i> Ubah Pengaturan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
