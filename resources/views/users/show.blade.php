@extends('layouts.admin-modern')

@section('page-title', 'Detail Pengguna')
@section('page-subtitle', 'Informasi lengkap tentang pengguna')

@section('main-content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="modern-card animate-fade-in">
            <div class="card-body text-center py-5">
                @if ($user->profile_picture)
                    <img src="{{ asset('/storage/' . $user->profile_picture) }}" alt="Foto Profil" class="rounded-circle mb-4" style="width: 150px; height: 150px; object-fit: cover;">
                @else
                    <div class="mx-auto mb-4" style="width: 150px; height: 150px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 60px; color: white; font-weight: 600;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                @endif
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                <span class="modern-badge badge-primary">Pengguna</span>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="modern-card animate-fade-in" style="animation-delay: 0.1s">
            <div class="card-header">
                <h5><i class="fas fa-user"></i> Informasi Pengguna</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <small class="text-muted d-block">ID Pengguna</small>
                            <span class="fw-semibold">#{{ $user->id }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">NIK</small>
                            <span class="fw-semibold">{{ $user->nik ?? '-' }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Tanggal Lahir</small>
                            <span class="fw-semibold">
                                @if ($user->date_of_birth)
                                    {{ \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d') }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Alamat</small>
                            <span class="fw-semibold">{{ $user->address ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <small class="text-muted d-block">No. Telepon</small>
                            <span class="fw-semibold">{{ $user->phone ?? '-' }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">No. Telepon Darurat</small>
                            <span class="fw-semibold">{{ $user->emergency_phone ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <a href="{{ route('users.index') }}" class="btn btn-modern btn-outline-modern">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
    </a>
    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-modern btn-danger-modern">
            <i class="fas fa-trash"></i> Hapus Pengguna
        </button>
    </form>
</div>
@endsection