@extends('layouts.admin-modern')

@section('page-title', 'Detail Transaksi')
@section('page-subtitle', 'Lihat rincian transaksi pembayaran')

@section('main-content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="modern-card animate-fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-receipt"></i> Rincian Transaksi</h5>
                @if ($transaction->status_pesanan === 'Complete')
                    <span class="modern-badge badge-success">Complete</span>
                @else
                    <span class="modern-badge badge-warning">Incomplete</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <small class="text-muted d-block">ID Transaksi</small>
                            <span class="fw-semibold">#{{ $transaction->id }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">ID Pesanan</small>
                            <code class="bg-light px-2 py-1 rounded">{{ $transaction->id_pesanan }}</code>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Metode Pembayaran</small>
                            <span class="fw-semibold">{{ $transaction->payment_method_name }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <small class="text-muted d-block">Total Bayar</small>
                            <span class="fw-semibold fs-4 text-primary">Rp {{ number_format($transaction->total_bayar, 0, ',', '.') }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Waktu Pembayaran</small>
                            <span class="fw-semibold">{{ $transaction->waktu_pembayaran ? \Carbon\Carbon::parse($transaction->waktu_pembayaran)->format('d M Y, H:i') : 'Belum dibayar' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="modern-card animate-fade-in" style="animation-delay: 0.1s">
            <div class="card-header">
                <h5><i class="fas fa-image"></i> Bukti Pembayaran</h5>
            </div>
            <div class="card-body text-center">
                @if ($transaction->bukti)
                    <img src="{{ asset('/storage/' . $transaction->bukti) }}" alt="Bukti Pembayaran" class="img-fluid rounded" style="max-width: 100%;">
                @else
                    <div class="py-5 text-muted">
                        <i class="fas fa-image fa-3x mb-3 d-block"></i>
                        <p class="mb-0">Tidak ada bukti pembayaran</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('transactions.index') }}" class="btn btn-modern btn-outline-modern">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
    </a>
    {{-- Verify button removed - payment verified automatically via Midtrans --}}
</div>
@endsection
