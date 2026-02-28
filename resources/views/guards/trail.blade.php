@extends('layouts.guards')

@section('page-title', 'Kelola Jalur')
@section('page-subtitle', 'Update informasi jalur pendakian ' . $trail->nama)

@section('content')
    <div class="row g-4">
        <!-- Form Update -->
        <div class="col-lg-8">
            <div class="modern-card animate-fade-in">
                <div class="card-header">
                    <h5><i class="fas fa-edit"></i> Update Informasi Jalur</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('guards.trail.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label-modern">Deskripsi Jalur</label>
                            <textarea name="deskripsi" class="form-control form-modern @error('deskripsi') is-invalid @enderror" 
                                rows="6" placeholder="Masukkan deskripsi jalur...">{{ old('deskripsi', $trail->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                Jelaskan kondisi jalur, tingkat kesulitan, dan tips untuk pendaki
                            </small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-modern">Link Map Basecamp</label>
                            <div class="input-group">
                                <span class="input-group-text" style="border-radius: 10px 0 0 10px; border: 1px solid #e2e8f0;">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                </span>
                                <input type="text" name="map_basecamp"
                                    class="form-control form-modern @error('map_basecamp') is-invalid @enderror"
                                    style="border-radius: 0 10px 10px 0;"
                                    value="{{ old('map_basecamp', $trail->map_basecamp) }}"
                                    placeholder="https://maps.google.com/...">
                                @error('map_basecamp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-link me-1"></i>
                                Link Google Maps lokasi basecamp
                            </small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-modern">Upload Gambar Jalur</label>
                            <div class="upload-area p-4 border-2 border-dashed rounded-3 text-center" 
                                style="border-color: #e2e8f0; background: #f8fafc;">
                                <input type="file" name="gambar_jalur" id="gambar_jalur"
                                    class="d-none @error('gambar_jalur') is-invalid @enderror" accept="image/*"
                                    onchange="previewImage(this)">
                                <label for="gambar_jalur" class="mb-0 cursor-pointer" style="cursor: pointer;">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3 d-block"></i>
                                    <p class="mb-1 fw-medium">Klik untuk upload gambar baru</p>
                                    <small class="text-muted">JPG, PNG maksimal 2MB</small>
                                </label>
                            </div>
                            @error('gambar_jalur')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            
                            @if ($trail->gambar_jalur)
                                <div class="mt-3">
                                    <label class="form-label-modern">Gambar Saat Ini</label>
                                    <div class="position-relative d-inline-block">
                                        <img src="{{ asset('storage/images/' . $trail->gambar_jalur) }}" 
                                            alt="Gambar Jalur" id="preview-image"
                                            class="rounded-3" style="max-height: 200px; object-fit: cover;">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-modern btn-primary-modern">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('guards.dashboard') }}" class="btn btn-modern btn-outline-modern">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Detail Sidebar -->
        <div class="col-lg-4">
            <div class="modern-card animate-fade-in" style="animation-delay: 0.1s">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Detail Jalur</h5>
                </div>
                <div class="card-body">
                    <div class="detail-item mb-3 pb-3 border-bottom">
                        <label class="text-muted small mb-1 d-block">Nama Jalur</label>
                        <p class="mb-0 fw-semibold">{{ $trail->nama }}</p>
                    </div>

                    <div class="detail-item mb-3 pb-3 border-bottom">
                        <label class="text-muted small mb-1 d-block">Gunung</label>
                        <p class="mb-0 fw-semibold">
                            <i class="fas fa-mountain text-primary me-1"></i>
                            {{ $trail->gunung->nama }}
                        </p>
                    </div>

                    <div class="detail-item mb-3 pb-3 border-bottom">
                        <label class="text-muted small mb-1 d-block">Lokasi</label>
                        <div class="small">
                            <p class="mb-1"><i class="fas fa-map me-1 text-muted"></i> {{ $trail->province->name }}</p>
                            <p class="mb-1 ms-3">{{ $trail->regency->name }}</p>
                            <p class="mb-1 ms-3">{{ $trail->district->name }}</p>
                            <p class="mb-0 ms-3">{{ $trail->village->name }}</p>
                        </div>
                    </div>

                    <div class="row g-3 mb-3 pb-3 border-bottom">
                        <div class="col-6">
                            <label class="text-muted small mb-1 d-block">Jarak</label>
                            <p class="mb-0 fw-semibold">
                                <i class="fas fa-route text-info me-1"></i>
                                {{ $trail->jarak }} km
                            </p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small mb-1 d-block">Biaya</label>
                            <p class="mb-0 fw-bold" style="color: var(--primary-color);">
                                Rp {{ number_format($trail->biaya, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <div class="alert alert-modern alert-info mb-0" style="background: #dbeafe; color: #1e40af;">
                        <i class="fas fa-info-circle"></i>
                        <small>Data di atas hanya dapat diubah oleh admin sistem</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('preview-image');
                    if (preview) {
                        preview.src = e.target.result;
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    @endpush
@endsection

