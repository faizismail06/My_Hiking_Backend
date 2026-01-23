@extends('layouts.app')

@section('content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">    
</head>
<body style="background: #117958">
    <div class="container bg-white p-4 rounded">
        
                    <h1 class="text-start my-4" style="font-weight: bold; color: black;">Tambah Gunung</h1>
                    <form action="{{ route('gunung.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Nama Gunung</label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ old('nama') }}" placeholder="Masukkan Nama Gunung">
                            
                            @error('nama')
                                <div class="alert alert-danger mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Dropdown Provinsi -->
                        <div class="form-group mb-3">
                            <label for="province_id" class="font-weight-bold">Provinsi</label>
                            <select id="province_id" name="province_id" class="select2 form-control @error('province_id') is-invalid @enderror">
                                <option value="" disabled selected>Pilih Provinsi</option>
                                @foreach($province_id as $province)
                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                @endforeach
                            </select>
                            @error('province_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Dropdown Kabupaten -->
                        <div class="form-group mb-3">
                            <label for="regency_id" class="font-weight-bold">Kabupaten</label>
                            <select id="regency_id" name="regency_id" class="form-control @error('regency_id') is-invalid @enderror">
                                <option value="" disabled selected>Pilih Kabupaten</option>
                                @foreach($regency_id as $regency)
                                    <option value="{{ $regency->id }}">{{ $regency->name }}</option>
                                @endforeach
                            </select>
                            @error('regency_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Dropdown Kecamatan -->
                        <div class="form-group mb-3">
                            <label for="district_id" class="font-weight-bold">Kecamatan</label>
                            <select id="district_id" name="district_id" class="form-control @error('district_id') is-invalid @enderror">
                                <option value="" disabled selected>Pilih Kecamatan</option>
                                @foreach($district_id as $district)
                                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                                @endforeach
                            </select>
                            @error('district_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Dropdown Desa -->
                        <div class="form-group mb-3">
                            <label for="village_id" class="font-weight-bold">Desa</label>
                            <select id="village_id" name="village_id" class="form-control @error('village_id') is-invalid @enderror">
                                <option value="" disabled selected>Pilih Desa</option>
                                @foreach($village_id as $village)
                                    <option value="{{ $village->id }}">{{ $village->name }}</option>
                                @endforeach
                            </select>
                            @error('village_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Ketinggian</label>
                            <input type="number" class="form-control @error('ketinggian') is-invalid @enderror" name="ketinggian" value="{{ old('ketinggian') }}" placeholder="Masukkan Ketinggian Gunung">
                            
                            @error('ketinggian')
                                <div class="alert alert-danger mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" name="deskripsi" rows="5" placeholder="Masukkan Deskripsi Gunung">{{ old('deskripsi') }}</textarea>
                            
                            @error('deskripsi')
                                <div class="alert alert-danger mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Gambar</label>
                            <input type="file" class="form-control @error('gambar_gunung') is-invalid @enderror" name="gambar_gunung">
                            
                            @error('gambar_gunung')
                                <div class="alert alert-danger mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Tombol Simpan dan Batal -->
                        <div class="d-flex justify-content-start">
                            <button type="submit" class="btn" style="background-color: #117958; color: white; border: none; margin-right: 10px;">Simpan</button>
                            <a href="{{ route('gunung.index') }}" class="btn btn-danger">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
 
        // Ketika provinsi dipilih
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

        // Ketika kabupaten dipilih
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

        // Ketika kecamatan dipilih
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
