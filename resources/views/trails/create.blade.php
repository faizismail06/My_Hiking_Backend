@extends('layouts.admin-modern')

@section('page-title', 'Tambah Jalur')
@section('page-subtitle', 'Tambahkan jalur pendakian baru ke sistem')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header">
        <h5><i class="fas fa-route"></i> Form Tambah Jalur</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('trails.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Informasi Jalur</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="id_gunung" class="form-label">Gunung <span class="text-danger">*</span></label>
                    <select id="id_gunung" name="id_gunung" class="form-select @error('id_gunung') is-invalid @enderror">
                        <option value="" disabled selected>Pilih Gunung</option>
                        @foreach ($pegunungan as $gunung)
                            <option value="{{ $gunung->id }}">{{ $gunung->nama }}</option>
                        @endforeach
                    </select>
                    @error('id_gunung')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Jalur <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Masukkan nama jalur">
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="province_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                    <select id="province_id" name="province_id" class="form-select @error('province_id') is-invalid @enderror">
                        <option value="" disabled selected>Pilih Provinsi</option>
                        @foreach ($province_id as $province)
                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                        @endforeach
                    </select>
                    @error('province_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="regency_id" class="form-label">Kabupaten <span class="text-danger">*</span></label>
                    <select id="regency_id" name="regency_id" class="form-select @error('regency_id') is-invalid @enderror">
                        <option value="" disabled selected>Pilih Kabupaten</option>
                        @foreach ($regency_id as $regency)
                            <option value="{{ $regency->id }}">{{ $regency->name }}</option>
                        @endforeach
                    </select>
                    @error('regency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="district_id" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                    <select id="district_id" name="district_id" class="form-select @error('district_id') is-invalid @enderror">
                        <option value="" disabled selected>Pilih Kecamatan</option>
                        @foreach ($district_id as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </select>
                    @error('district_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="village_id" class="form-label">Desa <span class="text-danger">*</span></label>
                    <select id="village_id" name="village_id" class="form-select @error('village_id') is-invalid @enderror">
                        <option value="" disabled selected>Pilih Desa</option>
                        @foreach ($village_id as $village)
                            <option value="{{ $village->id }}">{{ $village->name }}</option>
                        @endforeach
                    </select>
                    @error('village_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Jarak (km) <span class="text-danger">*</span></label>
                    <input type="text" name="jarak" class="form-control @error('jarak') is-invalid @enderror" value="{{ old('jarak') }}" placeholder="5.5">
                    @error('jarak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Biaya (Rp) <span class="text-danger">*</span></label>
                    <input type="text" name="biaya" class="form-control @error('biaya') is-invalid @enderror" value="{{ old('biaya') }}" placeholder="25000">
                    @error('biaya')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Gambar Jalur</label>
                    <input type="file" class="form-control" id="gambar_jalur" name="gambar_jalur" accept="image/*">
                </div>

                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3" placeholder="Masukkan deskripsi jalur">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Link Map Basecamp</label>
                    <input type="text" name="map_basecamp" class="form-control @error('map_basecamp') is-invalid @enderror" value="{{ old('map_basecamp') }}" placeholder="https://maps.google.com/...">
                    @error('map_basecamp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">

            <h6 class="text-muted mb-3"><i class="fas fa-user-shield me-2"></i>Data Penjaga Jalur</h6>
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                Sistem akan otomatis membuat akun untuk penjaga jalur. Password default: <strong>password123</strong>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Penjaga <span class="text-danger">*</span></label>
                    <input type="text" name="penjaga_name" class="form-control @error('penjaga_name') is-invalid @enderror" value="{{ old('penjaga_name') }}" placeholder="Masukkan nama penjaga">
                    @error('penjaga_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Penjaga <span class="text-danger">*</span></label>
                    <input type="email" name="penjaga_email" class="form-control @error('penjaga_email') is-invalid @enderror" value="{{ old('penjaga_email') }}" placeholder="email@example.com">
                    @error('penjaga_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="penjaga_phone" class="form-control @error('penjaga_phone') is-invalid @enderror" value="{{ old('penjaga_phone') }}" placeholder="08xxxxxxxxxx">
                    @error('penjaga_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Alamat (Opsional)</label>
                    <textarea name="penjaga_address" class="form-control" rows="2" placeholder="Masukkan alamat penjaga">{{ old('penjaga_address') }}</textarea>
                </div>
            </div>

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

