@extends('layouts.guards')

@section('page-title', 'Detail Permintaan Darurat')
@section('page-subtitle', 'Informasi lengkap permintaan darurat')

@section('content')
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Emergency Info -->
        <div class="col-lg-8">
            <div class="modern-card mb-4 animate-fade-in {{ $panicRequest->status === 'pending' ? 'border-danger' : '' }}" 
                 style="{{ $panicRequest->status === 'pending' ? 'border: 2px solid #e74c3c;' : '' }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Permintaan Darurat #{{ $panicRequest->id }}
                    </h5>
                    @if($panicRequest->status === 'pending')
                        <span class="badge bg-danger fs-6 pulse-badge">Menunggu Respons</span>
                    @elseif($panicRequest->status === 'responding')
                        <span class="badge bg-warning text-dark fs-6">Sedang Ditangani</span>
                    @else
                        <span class="badge bg-success fs-6">Selesai</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-box p-3 rounded" style="background: rgba(231, 76, 60, 0.1);">
                                <label class="text-muted small d-block mb-1">Jenis Darurat</label>
                                <h4 class="text-danger mb-0">{{ $panicRequest->emergency_type }}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box p-3 rounded" style="background: rgba(52, 152, 219, 0.1);">
                                <label class="text-muted small d-block mb-1">Waktu Laporan</label>
                                <h5 class="text-primary mb-0">{{ $panicRequest->created_at->format('d M Y, H:i') }}</h5>
                                <small class="text-muted">{{ $panicRequest->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @if($panicRequest->description)
                        <div class="col-12">
                            <div class="info-box p-3 rounded" style="background: #f8f9fa;">
                                <label class="text-muted small d-block mb-1">Keterangan Tambahan</label>
                                <p class="mb-0">{{ $panicRequest->description }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Location with Map -->
                    <div class="mt-4">
                        <h6 class="mb-3"><i class="fas fa-map-marker-alt text-danger me-2"></i>Lokasi Terakhir Pendaki</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-box p-3 rounded bg-light">
                                    <label class="text-muted small d-block mb-1">Latitude</label>
                                    <h5 class="mb-0">{{ $panicRequest->latitude }}</h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box p-3 rounded bg-light">
                                    <label class="text-muted small d-block mb-1">Longitude</label>
                                    <h5 class="mb-0">{{ $panicRequest->longitude }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="https://www.google.com/maps?q={{ $panicRequest->latitude }},{{ $panicRequest->longitude }}" 
                               target="_blank" class="btn btn-primary">
                                <i class="fas fa-map me-2"></i>Buka di Google Maps
                            </a>
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $panicRequest->latitude }},{{ $panicRequest->longitude }}" 
                               target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-directions me-2"></i>Petunjuk Arah
                            </a>
                        </div>
                        
                        <!-- Embedded Map -->
                        <div class="mt-3 rounded overflow-hidden" style="height: 300px;">
                            <iframe 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                style="border:0" 
                                loading="lazy" 
                                allowfullscreen 
                                referrerpolicy="no-referrer-when-downgrade"
                                src="https://maps.google.com/maps?q={{ $panicRequest->latitude }},{{ $panicRequest->longitude }}&z=15&output=embed">
                            </iframe>
                        </div>
                    </div>

                    <!-- Response Timeline -->
                    @if($panicRequest->responded_at || $panicRequest->resolved_at)
                    <div class="mt-4">
                        <h6 class="mb-3"><i class="fas fa-history me-2"></i>Timeline Penanganan</h6>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <strong>Laporan Diterima</strong>
                                    <p class="text-muted small mb-0">{{ $panicRequest->created_at->format('d M Y, H:i:s') }}</p>
                                </div>
                            </div>
                            @if($panicRequest->responded_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <strong>Direspons oleh {{ $panicRequest->responder->name ?? 'N/A' }}</strong>
                                    <p class="text-muted small mb-0">{{ $panicRequest->responded_at->format('d M Y, H:i:s') }}</p>
                                </div>
                            </div>
                            @endif
                            @if($panicRequest->resolved_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <strong>Penanganan Selesai</strong>
                                    <p class="text-muted small mb-0">{{ $panicRequest->resolved_at->format('d M Y, H:i:s') }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="mt-4 pt-4 border-top d-flex gap-2 flex-wrap">
                        <a href="{{ route('guards.sar.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        @if($panicRequest->status === 'pending')
                            <form action="{{ route('guards.sar.respond', $panicRequest->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-hand-paper me-2"></i>Respons Permintaan Ini
                                </button>
                            </form>
                        @elseif($panicRequest->status === 'responding')
                            <form action="{{ route('guards.sar.resolve', $panicRequest->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-2"></i>Tandai Selesai
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- User & Order Info Sidebar -->
        <div class="col-lg-4">
            <!-- User Info -->
            <div class="modern-card mb-4 animate-fade-in" style="animation-delay: 0.1s;">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Pendaki</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <span style="color: white; font-size: 2rem; font-weight: 600;">
                                {{ strtoupper(substr($panicRequest->user->name ?? 'N', 0, 1)) }}
                            </span>
                        </div>
                        <h5 class="mt-3 mb-1">{{ $panicRequest->user->name ?? 'N/A' }}</h5>
                        <p class="text-muted small mb-0">{{ $panicRequest->user->email ?? 'N/A' }}</p>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Telepon</span>
                        <strong>{{ $panicRequest->user->phone ?? '-' }}</strong>
                    </div>
                    @if($panicRequest->user->phone)
                        <a href="tel:{{ $panicRequest->user->phone }}" class="btn btn-success w-100">
                            <i class="fas fa-phone me-2"></i>Hubungi Pendaki
                        </a>
                    @endif
                </div>
            </div>

            <!-- Order Info -->
            <div class="modern-card mb-4 animate-fade-in" style="animation-delay: 0.2s;">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Informasi Tiket</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Order ID</span>
                        <strong>#{{ $panicRequest->order_id }}</strong>
                    </div>
                    @if($panicRequest->order)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Gunung</span>
                            <strong>{{ $panicRequest->order->mountain->nama ?? 'N/A' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Jalur</span>
                            <strong>{{ $panicRequest->order->trail->nama ?? 'N/A' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tanggal Pendakian</span>
                            <strong>{{ $panicRequest->order->tanggal_naik ? \Carbon\Carbon::parse($panicRequest->order->tanggal_naik)->format('d M Y') : 'N/A' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Jumlah Anggota</span>
                            <strong>{{ $panicRequest->order->members->count() ?? 0 }} orang</strong>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Group Members -->
            @if($panicRequest->order && $panicRequest->order->members->count() > 0)
            <div class="modern-card animate-fade-in" style="animation-delay: 0.3s;">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Anggota Rombongan</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($panicRequest->order->members as $member)
                            <li class="list-group-item d-flex align-items-center gap-2">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary-light); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user" style="color: var(--primary-color); font-size: 12px;"></i>
                                </div>
                                <div>
                                    <strong class="d-block small">{{ $member->name }}</strong>
                                    <small class="text-muted">{{ $member->email ?? '-' }}</small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>

    <style>
        .pulse-badge {
            animation: pulse-badge 1.5s infinite;
        }
        @keyframes pulse-badge {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        .timeline-marker {
            position: absolute;
            left: -30px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .timeline-content {
            padding-left: 10px;
        }
    </style>
@endsection
