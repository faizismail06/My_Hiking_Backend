@extends('layouts.guards')

@section('page-title', 'Riwayat Pengunjung')
@section('page-subtitle', 'Data pendaki yang mengunjungi jalur ' . $trail->nama)

@section('content')
    <!-- Filter Section -->
    <div class="modern-card mb-4 animate-fade-in">
        <div class="card-header">
            <h5><i class="fas fa-filter"></i> Filter Pencarian</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('guards.history') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label-modern">ID Pesanan</label>
                        <input type="text" name="search" class="form-control form-modern" 
                            placeholder="Cari ID pesanan..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-modern">Status</label>
                        <select name="status" class="form-control form-modern">
                            <option value="">Semua Status</option>
                            <option value="Booking" {{ request('status') == 'Booking' ? 'selected' : '' }}>Booking</option>
                            <option value="Dikonfirmasi" {{ request('status') == 'Dikonfirmasi' ? 'selected' : '' }}>Dikonfirmasi</option>
                            <option value="Sedang Mendaki" {{ request('status') == 'Sedang Mendaki' ? 'selected' : '' }}>Sedang Mendaki</option>
                            <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-modern btn-primary-modern w-100">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ⚠️ Banner Overdue --}}
    @if($overdueCount > 0)
    <div class="animate-fade-in" style="animation-delay: 0.05s; margin-bottom: 1.2rem;">
        <div style="
            background: linear-gradient(135deg, #fff1f2 0%, #fee2e2 100%);
            border: 1.5px solid #fca5a5;
            border-left: 5px solid #dc2626;
            border-radius: 14px;
            padding: 1rem 1.3rem;
            box-shadow: 0 4px 18px rgba(220,38,38,0.08);
        ">
            <div class="d-flex align-items-center gap-3">
                <div style="
                    background: #dc2626;
                    border-radius: 10px;
                    width: 40px; height: 40px;
                    display: flex; align-items: center; justify-content: center;
                    flex-shrink: 0;
                ">
                    <i class="fas fa-exclamation-triangle" style="color:#fff; font-size:1rem;"></i>
                </div>
                <div>
                    <h6 style="color:#991b1b; font-weight:700; margin-bottom:2px;">
                        ⚠️ {{ $overdueCount }} Pendaki Belum Turun & Melebihi Batas Waktu
                    </h6>
                    <p style="color:#b91c1c; font-size:0.83rem; margin-bottom:0;">
                        Tandai dengan warna merah di tabel di bawah. Segera hubungi atau lakukan check-out manual.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Results Table --}}
    <div class="modern-card animate-fade-in" style="animation-delay: 0.1s">
        <div class="card-header">
            <h5><i class="fas fa-users"></i> Daftar Pengunjung</h5>
            <span class="badge-modern badge-done">{{ $orders->total() }} Total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Ketua Rombongan</th>
                            <th class="text-center">Tanggal Naik</th>
                            <th class="text-center">Tanggal Turun</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $p)
                            @php
                                $isOverdue = $p->status == 'Sedang Mendaki'
                                    && \Carbon\Carbon::parse($p->tanggal_turun)->startOfDay()->lt(\Carbon\Carbon::today());
                                $overdueDays = $isOverdue
                                    ? \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($p->tanggal_turun))
                                    : 0;
                            @endphp
                            <tr style="{{ $isOverdue ? 'background:#fff5f5;' : '' }}">
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill">#{{ $p->id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                            {{ strtoupper(substr($p->user ? $p->user->name : 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $p->user ? $p->user->name : 'Tidak Diketahui' }}</div>
                                            @if($p->user)
                                                <small class="text-muted">{{ $p->user->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-arrow-up text-success mb-1"></i>
                                        <span>{{ \Carbon\Carbon::parse($p->tanggal_naik)->format('d M Y') }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-arrow-down text-danger mb-1"></i>
                                        <span>{{ \Carbon\Carbon::parse($p->tanggal_turun)->format('d M Y') }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($p->status == 'Booking' || $p->status == 'Menunggu Konfirmasi')
                                        <span class="badge-modern badge-booking">{{ $p->status }}</span>
                                    @elseif ($p->status == 'Dikonfirmasi')
                                        <span class="badge-modern badge-verified">{{ $p->status }}</span>
                                    @elseif($p->status == 'Sedang Mendaki')
                                        <span class="badge-modern badge-hiking">{{ $p->status }}</span>
                                        @if($isOverdue)
                                            <br><span style="display:inline-block;margin-top:4px;background:#dc2626;color:#fff;border-radius:20px;padding:2px 10px;font-size:0.72rem;font-weight:700;">⚠ Overdue +{{ $overdueDays }}h</span>
                                        @endif
                                    @elseif($p->status == 'Selesai')
                                        <span class="badge-modern badge-done">{{ $p->status }}</span>
                                    @else
                                        <span class="badge-modern badge-cancelled">{{ $p->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('guards.order.detail', $p->id) }}" 
                                        class="btn btn-modern btn-primary-modern btn-sm">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-mountain fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                                        <h6>Belum Ada Data Pengunjung</h6>
                                        <p class="mb-0 small">Data akan muncul setelah ada pendaki yang booking jalur ini</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($orders->hasPages())
            <div class="card-body border-top">
                <div class="d-flex justify-content-center">
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
@endsection



