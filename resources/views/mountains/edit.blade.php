@extends('layouts.admin-modern')

@section('page-title', 'Edit Gunung')
@section('page-subtitle', 'Perbarui data gunung')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header">
        <h5><i class="fas fa-edit"></i> Form Edit Gunung</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('mountains.update', $mountain->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Gunung <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ old('nama', $mountain->nama) }}" placeholder="Masukkan nama gunung">
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ketinggian (mdpl) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('ketinggian') is-invalid @enderror" name="ketinggian" value="{{ old('ketinggian', $mountain->ketinggian) }}" placeholder="Masukkan ketinggian">
                    @error('ketinggian')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="province_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                    <select id="province_id" name="province_id" class="form-select @error('province_id') is-invalid @enderror">
                        <option value="" disabled>Pilih Provinsi</option>
                        @foreach($province_id as $province)
                            <option value="{{ $province->id }}" {{ old('province_id', $mountain->province_id) == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                        @endforeach
                    </select>
                    @error('province_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="regency_id" class="form-label">Kabupaten <span class="text-danger">*</span></label>
                    <select id="regency_id" name="regency_id" class="form-select @error('regency_id') is-invalid @enderror">
                        <option value="" disabled>Pilih Kabupaten</option>
                        @foreach($regency_id as $regency)
                            <option value="{{ $regency->id }}" {{ old('regency_id', $mountain->regency_id) == $regency->id ? 'selected' : '' }}>{{ $regency->name }}</option>
                        @endforeach
                    </select>
                    @error('regency_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="district_id" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                    <select id="district_id" name="district_id" class="form-select @error('district_id') is-invalid @enderror">
                        <option value="" disabled>Pilih Kecamatan</option>
                        @foreach($district_id as $district)
                            <option value="{{ $district->id }}" {{ old('district_id', $mountain->district_id) == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                        @endforeach
                    </select>
                    @error('district_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="village_id" class="form-label">Desa <span class="text-danger">*</span></label>
                    <select id="village_id" name="village_id" class="form-select @error('village_id') is-invalid @enderror">
                        <option value="" disabled>Pilih Desa</option>
                        @foreach($village_id as $village)
                            <option value="{{ $village->id }}" {{ old('village_id', $mountain->village_id) == $village->id ? 'selected' : '' }}>{{ $village->name }}</option>
                        @endforeach
                    </select>
                    @error('village_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" name="deskripsi" rows="4" placeholder="Masukkan deskripsi gunung">{{ old('deskripsi', $mountain->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Gambar Gunung</label>
                    <input type="file" class="form-control @error('gambar_gunung') is-invalid @enderror" name="gambar_gunung" accept="image/*">
                    @error('gambar_gunung')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if (!empty($mountain->gambar_gunung))
                        <div class="mt-2">
                            <img src="{{ asset('/storage/images/' . $mountain->gambar_gunung) }}" alt="Gambar Gunung" class="rounded" style="max-width: 200px;">
                        </div>
                    @endif
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('mountains.index') }}" class="btn btn-modern btn-outline-modern">Batal</a>
                <button type="submit" class="btn btn-modern btn-primary-modern">
                    <i class="fas fa-save"></i> Update
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
