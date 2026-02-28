@extends('layouts.admin-modern')

@section('page-title', 'Edit Jalur')
@section('page-subtitle', 'Perbarui data jalur pendakian')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header">
        <h5><i class="fas fa-edit"></i> Form Edit Jalur</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('trails.update', $trail->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Informasi Jalur</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="id_gunung" class="form-label">Gunung <span class="text-danger">*</span></label>
                    <select id="id_gunung" name="id_gunung" class="form-select @error('id_gunung') is-invalid @enderror">
                        <option value="" disabled>Pilih Gunung</option>
                        @foreach ($mountains as $gunung)
                            <option value="{{ $gunung->id }}" {{ $gunung->id == $trail->id_gunung ? 'selected' : '' }}>{{ $gunung->nama }}</option>
                        @endforeach
                    </select>
                    @error('id_gunung')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Jalur <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $trail->nama) }}" placeholder="Masukkan nama jalur">
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="province_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                    <select id="province_id" name="province_id" class="form-select @error('province_id') is-invalid @enderror">
                        <option value="" disabled>Pilih Provinsi</option>
                        @foreach ($provinces as $province)
                            <option value="{{ $province->id }}" {{ $province->id == $trail->province_id ? 'selected' : '' }}>{{ $province->name }}</option>
                        @endforeach
                    </select>
                    @error('province_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="regency_id" class="form-label">Kabupaten <span class="text-danger">*</span></label>
                    <select id="regency_id" name="regency_id" class="form-select @error('regency_id') is-invalid @enderror">
                        <option value="" disabled>Pilih Kabupaten</option>
                        @foreach ($regencies as $regency)
                            <option value="{{ $regency->id }}" {{ $regency->id == $trail->regency_id ? 'selected' : '' }}>{{ $regency->name }}</option>
                        @endforeach
                    </select>
                    @error('regency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="district_id" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                    <select id="district_id" name="district_id" class="form-select @error('district_id') is-invalid @enderror">
                        <option value="" disabled>Pilih Kecamatan</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}" {{ $district->id == $trail->district_id ? 'selected' : '' }}>{{ $district->name }}</option>
                        @endforeach
                    </select>
                    @error('district_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="village_id" class="form-label">Desa <span class="text-danger">*</span></label>
                    <select id="village_id" name="village_id" class="form-select @error('village_id') is-invalid @enderror">
                        <option value="" disabled>Pilih Desa</option>
                        @foreach ($villages as $village)
                            <option value="{{ $village->id }}" {{ $village->id == $trail->village_id ? 'selected' : '' }}>{{ $village->name }}</option>
                        @endforeach
                    </select>
                    @error('village_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Jarak (km) <span class="text-danger">*</span></label>
                    <input type="text" name="jarak" class="form-control @error('jarak') is-invalid @enderror" value="{{ old('jarak', $trail->jarak) }}" placeholder="5.5">
                    @error('jarak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Biaya (Rp) <span class="text-danger">*</span></label>
                    <input type="text" name="biaya" class="form-control @error('biaya') is-invalid @enderror" value="{{ old('biaya', $trail->biaya) }}" placeholder="25000">
                    @error('biaya')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Gambar Jalur</label>
                    <input type="file" class="form-control" id="gambar_jalur" name="gambar_jalur" accept="image/*">
                    @if($trail->gambar_jalur)
                        <div class="mt-2">
                            <img src="{{ asset('/storage/images/' . $trail->gambar_jalur) }}" alt="Gambar" class="rounded" style="max-width: 100px;">
                        </div>
                    @endif
                </div>

                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3" placeholder="Masukkan deskripsi jalur">{{ old('deskripsi', $trail->deskripsi) }}</textarea>
                    @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Link Map Basecamp</label>
                    <input type="text" name="map_basecamp" class="form-control @error('map_basecamp') is-invalid @enderror" value="{{ old('map_basecamp', $trail->map_basecamp) }}" placeholder="https://maps.google.com/...">
                    @error('map_basecamp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $trail->latitude) }}" placeholder="-7.2575">
                    @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $trail->longitude) }}" placeholder="112.7521">
                    @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            @if ($trail->penjaga)
            <hr class="my-4">

            <h6 class="text-muted mb-3"><i class="fas fa-user-shield me-2"></i>Data Penjaga Jalur</h6>
            <div class="alert alert-info mb-3">
                <strong>Penjaga Saat Ini:</strong> {{ $trail->penjaga->name }} ({{ $trail->penjaga->email }})
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Penjaga</label>
                    <input type="text" name="penjaga_name" class="form-control @error('penjaga_name') is-invalid @enderror" value="{{ old('penjaga_name', $trail->penjaga->name) }}" placeholder="Masukkan nama penjaga">
                    @error('penjaga_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Penjaga</label>
                    <input type="email" name="penjaga_email" class="form-control @error('penjaga_email') is-invalid @enderror" value="{{ old('penjaga_email', $trail->penjaga->email) }}" placeholder="email@example.com">
                    @error('penjaga_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="penjaga_phone" class="form-control @error('penjaga_phone') is-invalid @enderror" value="{{ old('penjaga_phone', $trail->penjaga->phone) }}" placeholder="08xxxxxxxxxx">
                    @error('penjaga_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Alamat (Opsional)</label>
                    <textarea name="penjaga_address" class="form-control" rows="2" placeholder="Masukkan alamat penjaga">{{ old('penjaga_address', $trail->penjaga->address) }}</textarea>
                </div>
            </div>
            @endif

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('trails.index') }}" class="btn btn-modern btn-outline-modern">Batal</a>
                <button type="submit" class="btn btn-modern btn-primary-modern">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#province_id').change(function() {
        let provinceId = $(this).val();
        $('#regency_id').empty().append('<option value="" disabled selected>Loading...</option>');
        $('#district_id').empty().append('<option value="" disabled selected>Pilih Kecamatan</option>');
        $('#village_id').empty().append('<option value="" disabled selected>Pilih Desa</option>');
        $.get(`/get-regencies/${provinceId}`, function(data) {
            $('#regency_id').empty().append('<option value="" disabled selected>Pilih Kabupaten</option>');
            $.each(data, function(index, regency) {
                $('#regency_id').append(`<option value="${regency.id}">${regency.name}</option>`);
            });
        });
    });

    $('#regency_id').change(function() {
        let regencyId = $(this).val();
        $('#district_id').empty().append('<option value="" disabled selected>Loading...</option>');
        $('#village_id').empty().append('<option value="" disabled selected>Pilih Desa</option>');
        $.get(`/get-districts/${regencyId}`, function(data) {
            $('#district_id').empty().append('<option value="" disabled selected>Pilih Kecamatan</option>');
            $.each(data, function(index, district) {
                $('#district_id').append(`<option value="${district.id}">${district.name}</option>`);
            });
        });
    });

    $('#district_id').change(function() {
        let districtId = $(this).val();
        $('#village_id').empty().append('<option value="" disabled selected>Loading...</option>');
        $.get(`/get-villages/${districtId}`, function(data) {
            $('#village_id').empty().append('<option value="" disabled selected>Pilih Desa</option>');
            $.each(data, function(index, village) {
                $('#village_id').append(`<option value="${village.id}">${village.name}</option>`);
            });
        });
    });
});
</script>
@endsection

