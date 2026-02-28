@extends('layouts.admin-modern')

@section('page-title', 'Detail Pesanan')
@section('page-subtitle', 'Informasi lengkap tentang pesanan pendakian')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-ticket-alt"></i> Pesanan #{{ $pesanan->id }}</h5>
        @if($pesanan->status == 'Booking')
            <span class="modern-badge badge-warning">Booking</span>
        @elseif($pesanan->status == 'Sedang Mendaki')
            <span class="modern-badge badge-success">Mendaki</span>
        @elseif($pesanan->status == 'Selesai')
            <span class="modern-badge badge-secondary">Selesai</span>
        @endif
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="text-muted mb-3"><i class="fas fa-user me-2"></i>Informasi Pendaki</h6>
                <div class="mb-3">
                    <small class="text-muted d-block">Ketua Rombongan</small>
                    <span class="fw-semibold">{{ $pesanan->user->name ?? 'Tidak Diketahui' }}</span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Anggota</small>
                    <ul class="list-unstyled mb-0">
                        @forelse ($pesanan->anggotaPesanan as $anggota)
                            <li><i class="fas fa-user text-muted me-2"></i>{{ $anggota->user->name ?? 'Tidak Diketahui' }}</li>
                        @empty
                            <li class="text-muted">Tidak ada anggota</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-3"><i class="fas fa-mountain me-2"></i>Informasi Pendakian</h6>
                <div class="mb-3">
                    <small class="text-muted d-block">Gunung</small>
                    <span class="fw-semibold">{{ $pesanan->gunung->nama ?? 'Tidak Diketahui' }}</span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Jalur</small>
                    <span class="fw-semibold">{{ $pesanan->jalur->nama ?? 'Tidak Diketahui' }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-3"><i class="fas fa-calendar me-2"></i>Jadwal</h6>
                <div class="mb-3">
                    <small class="text-muted d-block">Tanggal Naik</small>
                    <span class="fw-semibold">{{ $pesanan->tanggal_naik->format('d M Y') }}</span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Tanggal Turun</small>
                    <span class="fw-semibold">{{ $pesanan->tanggal_turun->format('d M Y') }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-3"><i class="fas fa-money-bill me-2"></i>Pembayaran</h6>
                <div class="mb-3">
                    <small class="text-muted d-block">Total Harga Tiket</small>
                    <span class="fw-semibold fs-4 text-primary">Rp {{ number_format($pesanan->total_harga_tiket, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <a href="{{ route('history.index') }}" class="btn btn-modern btn-outline-modern">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
    </a>
    @if($pesanan->status != 'Selesai')
        <form action="{{ route('history.updateStatus', $pesanan->id) }}" method="POST" class="d-inline">
            @csrf
            @method('PUT')
            @if($pesanan->status == 'Booking')
                <button type="submit" name="status" value="Sedang Mendaki" class="btn btn-modern btn-success-modern">
                    <i class="fas fa-hiking"></i> Ganti ke Mendaki
                </button>
            @elseif($pesanan->status == 'Sedang Mendaki')
                <button type="submit" name="status" value="Selesai" class="btn btn-modern btn-primary-modern">
                    <i class="fas fa-check"></i> Ganti ke Selesai
                </button>
            @endif
        </form>
    @endif
</div>
@endsection
