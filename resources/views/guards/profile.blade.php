@extends('layouts.guards')

@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi akun Anda')

@section('content')
    <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="modern-card animate-fade-in">
                <div class="card-body text-center py-5">
                    <div class="user-avatar mx-auto mb-4" style="width: 100px; height: 100px; font-size: 2.5rem;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <h4 class="fw-bold mb-1">{{ Auth::user()->name }}</h4>
                    <p class="text-muted mb-3">{{ Auth::user()->email }}</p>
                    <span class="badge-modern badge-done">
                        <i class="fas fa-shield-alt me-1"></i>
                        Penjaga Jalur
                    </span>

                    @if(Auth::user()->trailGuard)
                        <div class="mt-4 pt-4 border-top">
                            <div class="text-start">
                                <p class="text-muted small mb-2">Jalur yang Dikelola</p>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="icon" style="width: 40px; height: 40px; background: var(--primary-light); color: var(--primary-color); display: flex; align-items: center; justify-content: center; border-radius: 10px;">
                                        <i class="fas fa-route"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-semibold">{{ Auth::user()->trailGuard->nama }}</p>
                                        <small class="text-muted">{{ Auth::user()->trailGuard->gunung->nama ?? '-' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="modern-card mt-4 animate-fade-in" style="animation-delay: 0.1s">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Informasi Akun</h5>
                </div>
                <div class="card-body">
                    <div class="info-row d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">ID Pengguna</span>
                        <span class="fw-semibold">#{{ Auth::user()->id }}</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Role</span>
                        <span class="fw-semibold">Level {{ Auth::user()->level }}</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-2">
                        <span class="text-muted">Bergabung</span>
                        <span class="fw-semibold">{{ Auth::user()->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="col-lg-8">
            <div class="modern-card animate-fade-in" style="animation-delay: 0.2s">
                <div class="card-header">
                    <h5><i class="fas fa-user-edit"></i> Edit Profil</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('guards.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label-modern">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-modern @error('name') is-invalid @enderror" 
                                value="{{ old('name', Auth::user()->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label-modern">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-modern @error('email') is-invalid @enderror" 
                                value="{{ old('email', Auth::user()->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <h6 class="text-muted mb-3">
                            <i class="fas fa-lock me-2"></i>
                            Ubah Password (Opsional)
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label-modern">Password Saat Ini</label>
                                <input type="password" name="current_password" class="form-control form-modern @error('current_password') is-invalid @enderror">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-modern">Password Baru</label>
                                <input type="password" name="new_password" class="form-control form-modern @error('new_password') is-invalid @enderror">
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-modern">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control form-modern">
                            </div>
                        </div>

                        <div class="alert alert-modern alert-info mt-3" style="background: #dbeafe; color: #1e40af;">
                            <i class="fas fa-info-circle"></i>
                            <small>Kosongkan field password jika tidak ingin mengubah password</small>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="{{ route('guards.dashboard') }}" class="btn btn-modern btn-outline-modern">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-modern btn-primary-modern">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
