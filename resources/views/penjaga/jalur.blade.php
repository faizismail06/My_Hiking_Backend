@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Manajemen Jalur</h1>
            <a href="{{ route('penjaga.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Info Jalur -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">Update Informasi Jalur</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('penjaga.jalur.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label class="font-weight-bold">Deskripsi Jalur</label>
                                <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="6"
                                    placeholder="Masukkan deskripsi jalur...">{{ old('deskripsi', $jalur->deskripsi) }}</textarea>
                                @error('deskripsi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <small class="form-text text-muted">Jelaskan kondisi jalur, tingkat kesulitan, dan tips
                                    untuk pendaki</small>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Link Map Basecamp</label>
                                <input type="text" name="map_basecamp"
                                    class="form-control @error('map_basecamp') is-invalid @enderror"
                                    value="{{ old('map_basecamp', $jalur->map_basecamp) }}"
                                    placeholder="https://maps.google.com/...">
                                @error('map_basecamp')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <small class="form-text text-muted">Link Google Maps lokasi basecamp</small>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Upload Gambar Jalur</label>
                                <input type="file" name="gambar_jalur"
                                    class="form-control-file @error('gambar_jalur') is-invalid @enderror" accept="image/*">
                                @error('gambar_jalur')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                @if ($jalur->gambar_jalur)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/images/' . $jalur->gambar_jalur) }}" alt="Gambar Jalur"
                                            class="img-thumbnail" style="max-height: 200px;">
                                        <p class="text-muted mt-1">Gambar saat ini</p>
                                    </div>
                                @endif
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Detail Jalur (Read Only) -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-success text-white">
                        <h6 class="m-0 font-weight-bold">Detail Jalur</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="font-weight-bold text-muted">Nama Jalur</label>
                            <p class="mb-0">{{ $jalur->nama }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-bold text-muted">Gunung</label>
                            <p class="mb-0">{{ $jalur->gunung->nama }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-bold text-muted">Provinsi</label>
                            <p class="mb-0">{{ $jalur->province->name }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-bold text-muted">Kabupaten</label>
                            <p class="mb-0">{{ $jalur->regency->name }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-bold text-muted">Kecamatan</label>
                            <p class="mb-0">{{ $jalur->district->name }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-bold text-muted">Desa</label>
                            <p class="mb-0">{{ $jalur->village->name }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-bold text-muted">Jarak</label>
                            <p class="mb-0">{{ $jalur->jarak }} km</p>
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-bold text-muted">Biaya Pendakian</label>
                            <p class="mb-0 text-success font-weight-bold">Rp
                                {{ number_format($jalur->biaya, 0, ',', '.') }}</p>
                        </div>

                        <div class="alert alert-info">
                            <small><i class="fas fa-info-circle"></i> Data di atas hanya bisa diubah oleh admin</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
