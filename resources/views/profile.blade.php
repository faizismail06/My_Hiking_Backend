@extends('layouts.admin-modern')

@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi akun administrator')

@section('main-content')
<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-lg-4">
        <div class="modern-card animate-fade-in">
            <div class="card-body text-center py-5">
                <div class="profile-avatar mx-auto mb-4" style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 48px; color: white; font-weight: 600;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </div>
                <h4 class="mb-1">{{ Auth::user()->name }}</h4>
                <p class="text-muted mb-3">{{ Auth::user()->email }}</p>
                <span class="modern-badge badge-primary">Administrator</span>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="col-lg-8">
        <div class="modern-card animate-fade-in" style="animation-delay: 0.1s">
            <div class="card-header">
                <h5><i class="fas fa-user-edit"></i> Edit Profil</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Informasi Dasar</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Alamat Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-4">
                        <h6 class="text-muted mb-3"><i class="fas fa-lock me-2"></i>Ubah Password</h6>
                        <p class="small text-muted mb-3">Kosongkan jika tidak ingin mengubah password</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="current_password">Password Saat Ini</label>
                                <input type="password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" name="current_password">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="new_password">Password Baru</label>
                                <input type="password" id="new_password" class="form-control @error('new_password') is-invalid @enderror" name="new_password">
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="confirm_password">Konfirmasi Password</label>
                                <input type="password" id="confirm_password" class="form-control" name="password_confirmation">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('home') }}" class="btn btn-modern btn-outline-modern">Batal</a>
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
