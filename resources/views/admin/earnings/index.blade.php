@extends('layouts.admin-modern')

@section('page-title', 'Earnings & Withdrawal')
@section('page-subtitle', 'Kelola saldo pendapatan dan request penarikan dari penjaga jalur')

@section('main-content')
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6 animate-fade-in">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Pendapatan Kotor Penjaga</p>
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

        <div class="col-xl-3 col-md-6 animate-fade-in" style="animation-delay: 0.1s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Dana Diterima Penjaga</p>
                        <h3 class="value mb-0">
                            Rp {{ number_format($totalTransferredToGuards ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="icon success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 animate-fade-in" style="animation-delay: 0.2s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Fee Admin Masuk Sistem</p>
                        <h3 class="value mb-0">Rp {{ number_format($adminFeeCollected ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <div class="icon warning">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 animate-fade-in" style="animation-delay: 0.3s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Request Pending</p>
                        <h3 class="value mb-0">{{ $pendingRequests ?? 0 }}</h3>
                    </div>
                    <div class="icon info">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="modern-card animate-fade-in" style="animation-delay: 0.35s; background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);">
                <div class="card-body">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-7">
                            <h5 class="mb-2"><i class="fas fa-circle-info"></i> Alur Saldo Withdrawal</h5>
                            <p class="mb-0" style="color: #475569;">
                                Saat withdrawal selesai, <strong>jumlah request penuh</strong> mengurangi saldo penjaga.
                                Dari jumlah itu, <strong>dana bersih</strong> ditransfer ke penjaga dan <strong>fee admin</strong>
                                dicatat sebagai pendapatan sistem admin.
                            </p>
                        </div>
                        <div class="col-lg-5">
                            <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
                                <div style="background: white; border-radius: 16px; padding: 1rem; border: 1px solid #dbeafe;">
                                    <p class="label mb-1">Total Withdrawal Diproses</p>
                                    <h4 class="mb-0">Rp {{ number_format($totalProcessedWithdrawal ?? 0, 0, ',', '.') }}</h4>
                                </div>
                                <div style="background: white; border-radius: 16px; padding: 1rem; border: 1px solid #fde68a;">
                                    <p class="label mb-1">Nominal Pending</p>
                                    <h4 class="mb-0">Rp {{ number_format($pendingWithdrawalAmount ?? 0, 0, ',', '.') }}</h4>
                                </div>
                                <div style="background: white; border-radius: 16px; padding: 1rem; border: 1px solid #dcfce7;">
                                    <p class="label mb-1">Request Selesai</p>
                                    <h4 class="mb-0">{{ $completedRequests ?? 0 }}</h4>
                                </div>
                                <div style="background: white; border-radius: 16px; padding: 1rem; border: 1px solid #e0e7ff;">
                                    <p class="label mb-1">Request Disetujui</p>
                                    <h4 class="mb-0">{{ $approvedRequests ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
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
                                    <th>Pendapatan Kotor</th>
                                    <th>Saldo Sudah Diproses</th>
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
                                            <strong title="Total pendapatan kotor penjaga">Rp
                                                {{ number_format($guard->total_earnings ?? 0, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <span style="color: var(--success-color);" title="Jumlah saldo yang sudah keluar dari akun penjaga, termasuk fee admin">Rp
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
