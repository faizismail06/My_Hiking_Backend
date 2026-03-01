@extends('layouts.guards')

@section('page-title', 'SAR Dashboard')
@section('page-subtitle', 'Kelola permintaan darurat dari pendaki')

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

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6 animate-fade-in" style="animation-delay: 0.1s">
            <div class="stat-card" style="border-left: 4px solid #e74c3c;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Menunggu Respons</p>
                        <h3 class="value mb-0">{{ $countPending }}</h3>
                    </div>
                    <div class="icon" style="background: rgba(231, 76, 60, 0.15); color: #e74c3c;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 animate-fade-in" style="animation-delay: 0.2s">
            <div class="stat-card" style="border-left: 4px solid #f39c12;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Sedang Ditangani</p>
                        <h3 class="value mb-0">{{ $countResponding }}</h3>
                    </div>
                    <div class="icon" style="background: rgba(243, 156, 18, 0.15); color: #f39c12;">
                        <i class="fas fa-ambulance"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 animate-fade-in" style="animation-delay: 0.3s">
            <div class="stat-card" style="border-left: 4px solid #27ae60;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Selesai</p>
                        <h3 class="value mb-0">{{ $countResolved }}</h3>
                    </div>
                    <div class="icon" style="background: rgba(39, 174, 96, 0.15); color: #27ae60;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="modern-card mb-4 animate-fade-in">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('guards.sar.index', ['filter' => 'all']) }}" 
                   class="btn {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-list me-1"></i> Semua
                </a>
                <a href="{{ route('guards.sar.index', ['filter' => 'pending']) }}" 
                   class="btn {{ $filter === 'pending' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="fas fa-clock me-1"></i> Menunggu ({{ $countPending }})
                </a>
                <a href="{{ route('guards.sar.index', ['filter' => 'responding']) }}" 
                   class="btn {{ $filter === 'responding' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="fas fa-ambulance me-1"></i> Ditangani ({{ $countResponding }})
                </a>
                <a href="{{ route('guards.sar.index', ['filter' => 'resolved']) }}" 
                   class="btn {{ $filter === 'resolved' ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="fas fa-check-circle me-1"></i> Selesai ({{ $countResolved }})
                </a>
            </div>
        </div>
    </div>

    <!-- Panic Requests List -->
    <div class="modern-card animate-fade-in">
        <div class="card-header">
            <h5><i class="fas fa-exclamation-triangle text-danger me-2"></i>Daftar Permintaan Darurat</h5>
        </div>
        <div class="card-body p-0">
            @forelse($panicRequests as $panic)
                <div class="panic-item p-4 border-bottom {{ $panic->status === 'pending' ? 'bg-danger-subtle' : '' }}">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="panic-icon {{ $panic->status === 'pending' ? 'pulse-animation' : '' }}" 
                                     style="width: 50px; height: 50px; border-radius: 50%; 
                                            background: {{ $panic->status === 'pending' ? '#e74c3c' : ($panic->status === 'responding' ? '#f39c12' : '#27ae60') }}; 
                                            display: flex; align-items: center; justify-content: center; color: white;">
                                    <i class="fas {{ $panic->status === 'pending' ? 'fa-exclamation' : ($panic->status === 'responding' ? 'fa-ambulance' : 'fa-check') }} fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">{{ $panic->emergency_type }}</h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="fas fa-user me-1"></i>{{ $panic->user->name ?? 'N/A' }}
                                    </p>
                                    <p class="mb-1 text-muted small">
                                        <i class="fas fa-ticket-alt me-1"></i>Order #{{ $panic->order_id }}
                                    </p>
                                    <p class="mb-0 text-muted small">
                                        <i class="fas fa-clock me-1"></i>{{ $panic->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            @if($panic->status === 'pending')
                                <span class="badge bg-danger fs-6">Menunggu Respons</span>
                            @elseif($panic->status === 'responding')
                                <span class="badge bg-warning text-dark fs-6">Sedang Ditangani</span>
                                <p class="small mt-1 mb-0 text-muted">oleh {{ $panic->responder->name ?? 'N/A' }}</p>
                            @else
                                <span class="badge bg-success fs-6">Selesai</span>
                            @endif
                        </div>
                        <div class="col-md-3 text-end">
                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                <a href="{{ route('guards.sar.show', $panic->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                                @if($panic->status === 'pending')
                                    <form action="{{ route('guards.sar.respond', $panic->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="fas fa-hand-paper me-1"></i>Respons
                                        </button>
                                    </form>
                                @elseif($panic->status === 'responding')
                                    <form action="{{ route('guards.sar.resolve', $panic->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check me-1"></i>Selesai
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($panic->description)
                        <div class="mt-2 pt-2 border-top">
                            <p class="mb-0 small"><strong>Keterangan:</strong> {{ $panic->description }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-muted">Tidak ada permintaan darurat</h5>
                    <p class="text-muted small">Semua pendaki dalam kondisi aman</p>
                </div>
            @endforelse
        </div>
    </div>

    <style>
        .pulse-animation {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(231, 76, 60, 0); }
            100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); }
        }
        .bg-danger-subtle {
            background-color: rgba(231, 76, 60, 0.05) !important;
        }
        .panic-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
@endsection
