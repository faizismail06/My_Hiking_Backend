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
                <value>{{ rtrim(rtrim(number_format($trail->jarak, 2, '.', ''), '0'), '.') }} km</value>
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
                        <small class="text-muted">(sudah lunas)</small>
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
                        <small class="text-muted">(sudah lunas)</small>
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

    {{-- ⚠️ Banner Overdue Pendaki --}}
    @if($overdueCount > 0)
    <div class="animate-fade-in" style="animation-delay: 0.35s; margin-bottom: 1.5rem;">
        <div style="
            background: linear-gradient(135deg, #fff1f2 0%, #fee2e2 100%);
            border: 1.5px solid #fca5a5;
            border-left: 5px solid #dc2626;
            border-radius: 14px;
            padding: 1.1rem 1.4rem;
            box-shadow: 0 4px 18px rgba(220,38,38,0.08);
        ">
            <div class="d-flex align-items-start gap-3">
                <div style="
                    background: #dc2626;
                    border-radius: 10px;
                    width: 42px; height: 42px;
                    display: flex; align-items: center; justify-content: center;
                    flex-shrink: 0;
                ">
                    <i class="fas fa-exclamation-triangle" style="color:#fff; font-size:1.1rem;"></i>
                </div>
                <div style="flex:1;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h6 style="color:#991b1b; font-weight:700; margin-bottom:2px;">
                                ⚠️ {{ $overdueCount }} Pendaki Melewati Batas Turun!
                            </h6>
                            <p style="color:#b91c1c; font-size:0.85rem; margin-bottom:0.6rem;">
                                Pendaki berikut sudah melewati tanggal turun namun belum melakukan check-out:
                            </p>
                        </div>
                        <a href="{{ route('guards.history') }}" style="
                            background: #dc2626; color: #fff;
                            padding: 6px 14px; border-radius: 8px;
                            font-size: 0.8rem; font-weight: 600;
                            text-decoration: none; white-space: nowrap;
                        ">
                            <i class="fas fa-list me-1"></i> Lihat Riwayat
                        </a>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($overdueOrders as $ov)
                            @php
                                $overdueDays = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($ov->tanggal_turun));
                            @endphp
                            <span style="
                                background: #fef2f2;
                                border: 1px solid #fca5a5;
                                border-radius: 20px;
                                padding: 4px 12px;
                                font-size: 0.8rem;
                                color: #991b1b;
                                font-weight: 600;
                                display: inline-flex;
                                align-items: center;
                                gap: 6px;
                            ">
                                <i class="fas fa-user" style="font-size:0.7rem;"></i>
                                {{ $ov->user ? $ov->user->name : 'Pendaki #'.$ov->id }}
                                <span style="background:#dc2626;color:#fff;border-radius:10px;padding:1px 7px;font-size:0.7rem;">
                                    +{{ $overdueDays }} hari
                                </span>
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Quick Actions --}}
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
                            @php
                                $rowOverdue = $order->status == 'Sedang Mendaki'
                                    && \Carbon\Carbon::parse($order->tanggal_turun)->startOfDay()->lt(\Carbon\Carbon::today());
                            @endphp
                            <tr style="{{ $rowOverdue ? 'background: #fff5f5;' : '' }}">
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
                                    @php
                                        $isOverdue = $order->status == 'Sedang Mendaki'
                                            && \Carbon\Carbon::parse($order->tanggal_turun)->startOfDay()->lt(\Carbon\Carbon::today());
                                    @endphp
                                    @if ($order->status == 'Menunggu Konfirmasi')
                                        <span class="badge-modern badge-pending">{{ $order->status }}</span>
                                    @elseif($order->status == 'Dikonfirmasi')
                                        <span class="badge-modern badge-verified">{{ $order->status }}</span>
                                    @elseif($order->status == 'Sedang Mendaki')
                                        <span class="badge-modern badge-hiking">{{ $order->status }}</span>
                                        @if($isOverdue)
                                            <br><span style="display:inline-block;margin-top:4px;background:#dc2626;color:#fff;border-radius:20px;padding:2px 10px;font-size:0.72rem;font-weight:700;">⚠ Overdue</span>
                                        @endif
                                    @elseif($order->status == 'Selesai')
                                        <span class="badge-modern badge-done">{{ $order->status }}</span>
                                    @else
                                        <span class="badge-modern badge-cancelled">{{ $order->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('guards.order.detail', $order->id) }}"
                                            class="btn btn-modern btn-outline-modern btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if ($order->status == 'Dikonfirmasi')
                                            <form action="{{ route('guards.checkin', $order->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-modern btn-success-modern btn-sm">
                                                    <i class="fas fa-sign-in-alt"></i> Check In
                                                </button>
                                            </form>
                                        @elseif($order->status == 'Sedang Mendaki')
                                            <form action="{{ route('guards.checkout', $order->id) }}" method="POST"
                                                class="d-inline">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let lastClimbersCount = null;
    let lastSosCount = null;
    let lastSyncTimes = {};

    async function checkDashboardUpdates() {
        try {
            const response = await fetch("{{ route('guards.monitoring.data') }}");
            const res = await response.json();
            if (res.success) {
                const climbers = res.climbers || [];
                const sosRequests = res.sos_requests || [];
                
                const currentClimbersCount = climbers.length;
                const currentSosCount = sosRequests.length;
                
                let syncTimesChanged = false;
                const currentSyncTimes = {};
                climbers.forEach(c => {
                    currentSyncTimes[c.order_id] = c.synced_at;
                    if (lastClimbersCount !== null && lastSyncTimes[c.order_id] !== c.synced_at) {
                        syncTimesChanged = true;
                    }
                });

                if (lastClimbersCount !== null) {
                    if (currentClimbersCount !== lastClimbersCount || 
                        currentSosCount !== lastSosCount || 
                        syncTimesChanged) {
                        console.log("Detecting database tracking updates. Reloading dashboard...");
                        window.location.reload();
                    }
                }

                lastClimbersCount = currentClimbersCount;
                lastSosCount = currentSosCount;
                lastSyncTimes = currentSyncTimes;
            }
        } catch (error) {
            console.error("Dashboard update check failed:", error);
        }
    }

    // Check every 10 seconds
    setInterval(checkDashboardUpdates, 10000);
    checkDashboardUpdates(); // Check once immediately
});
</script>
@endpush
