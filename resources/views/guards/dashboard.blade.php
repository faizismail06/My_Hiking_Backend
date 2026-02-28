@extends('layouts.guards')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan aktivitas jalur pendakian Anda')

@section('content')
    <!-- Trail Info Card -->
    <div class="trail-info-card mb-4 animate-fade-in">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 class="trail-name">{{ $trail->nama }}</h2>
                <p class="mountain-name"><i class="fas fa-mountain me-2"></i>{{ $trail->gunung->nama }}</p>
            </div>
            @if ($trail->gambar_jalur)
                <img src="{{ asset('storage/images/' . $trail->gambar_jalur) }}" alt="{{ $trail->nama }}"
                    style="width: 100px; height: 100px; object-fit: cover; border-radius: 12px; border: 3px solid rgba(255,255,255,0.3);">
            @endif
        </div>
        <div class="info-grid mt-3">
            <div class="info-item">
                <label>Lokasi</label>
                <value>{{ $trail->village->name }}, {{ $trail->district->name }}</value>
            </div>
            <div class="info-item">
                <label>Kabupaten</label>
                <value>{{ $trail->regency->name }}</value>
            </div>
            <div class="info-item">
                <label>Jarak Pendakian</label>
                <value>{{ $trail->jarak }} km</value>
            </div>
            <div class="info-item">
                <label>Biaya Pendakian</label>
                <value>Rp {{ number_format($trail->biaya, 0, ',', '.') }}</value>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6 animate-fade-in" style="animation-delay: 0.1s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Pengunjung Hari Ini</p>
                        <h3 class="value mb-0">{{ $visitorsToday }}</h3>
                    </div>
                    <div class="icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 animate-fade-in" style="animation-delay: 0.2s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Total Bulan Ini</p>
                        <h3 class="value mb-0">{{ $visitorsThisMonth }}</h3>
                    </div>
                    <div class="icon success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 animate-fade-in" style="animation-delay: 0.3s">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Pendapatan Bulan Ini</p>
                        <h3 class="value mb-0">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</h3>
                    </div>
                    <div class="icon warning">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3 animate-fade-in" style="animation-delay: 0.4s">
            <a href="{{ route('guards.scanner') }}" class="quick-action">
                <div class="icon" style="background: #fef3c7; color: #d97706;">
                    <i class="fas fa-qrcode"></i>
                </div>
                <h6>Scanner QR</h6>
                <p>Check in/out pendaki</p>
            </a>
        </div>
        <div class="col-md-6 col-lg-3 animate-fade-in" style="animation-delay: 0.5s">
            <a href="{{ route('guards.trail') }}" class="quick-action">
                <div class="icon" style="background: #dbeafe; color: #2563eb;">
                    <i class="fas fa-route"></i>
                </div>
                <h6>Kelola Jalur</h6>
                <p>Edit informasi jalur</p>
            </a>
        </div>
        <div class="col-md-6 col-lg-3 animate-fade-in" style="animation-delay: 0.6s">
            <a href="{{ route('guards.history') }}" class="quick-action">
                <div class="icon" style="background: #dcfce7; color: #16a34a;">
                    <i class="fas fa-history"></i>
                </div>
                <h6>Riwayat</h6>
                <p>Lihat data pengunjung</p>
            </a>
        </div>
        <div class="col-md-6 col-lg-3 animate-fade-in" style="animation-delay: 0.7s">
            <a href="{{ route('guards.revenue') }}" class="quick-action">
                <div class="icon" style="background: var(--primary-light); color: var(--primary-color);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h6>Laporan</h6>
                <p>Analisis pendapatan</p>
            </a>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="modern-card animate-fade-in" style="animation-delay: 0.8s">
        <div class="card-header">
            <h5><i class="fas fa-clipboard-list"></i> Pesanan Terbaru</h5>
            <a href="{{ route('guards.history') }}" class="btn btn-modern btn-outline-modern btn-sm">
                Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Tanggal Pesan</th>
                            <th>Nama Pendaki</th>
                            <th>Jumlah</th>
                            <th>Tanggal Naik</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-calendar text-muted"></i>
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($order->user->name, 0, 1)) }}
                                        </div>
                                        <span class="fw-medium">{{ $order->user->name }}</span>
                                    </div>
                                </td>
                                <td><span class="fw-semibold">{{ $order->orderMembers->count() }}</span> orang</td>
                                <td>{{ \Carbon\Carbon::parse($order->tanggal_naik)->format('d M Y') }}</td>
                                <td>
                                    @if ($order->status == 'Menunggu Konfirmasi')
                                        <span class="badge-modern badge-pending">{{ $order->status }}</span>
                                    @elseif($order->status == 'Dikonfirmasi')
                                        <span class="badge-modern badge-verified">{{ $order->status }}</span>
                                    @elseif($order->status == 'Sedang Mendaki')
                                        <span class="badge-modern badge-hiking">{{ $order->status }}</span>
                                    @elseif($order->status == 'Selesai')
                                        <span class="badge-modern badge-done">{{ $order->status }}</span>
                                    @else
                                        <span class="badge-modern badge-cancelled">{{ $order->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('guards.order.detail', $order->id) }}" class="btn btn-modern btn-outline-modern btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if ($order->status == 'Dikonfirmasi')
                                            <form action="{{ route('guards.checkin', $order->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-modern btn-success-modern btn-sm">
                                                    <i class="fas fa-sign-in-alt"></i> Check In
                                                </button>
                                            </form>
                                        @elseif($order->status == 'Sedang Mendaki')
                                            <form action="{{ route('guards.checkout', $order->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-modern btn-info-modern btn-sm">
                                                    <i class="fas fa-sign-out-alt"></i> Check Out
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        <p class="mb-0">Belum ada pesanan terbaru</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection



