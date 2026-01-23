@extends('layouts.app')

@section('content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body style="background: #117958">
<div class="container mt-4 mb-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded">
                <div class="card-body">
                    <h1 class="text-start my-4" style="font-weight: bold; color: black;">Edit Jalur</h1>
                    <form action="{{ route('jalur.update', $jalur->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Dropdown Nama Gunung -->
                        <div class="form-group mb-3">
                            <label for="id_gunung" class="font-weight-bold">Nama Gunung</label>
                            <select id="id_gunung" name="id_gunung" class="form-control @error('id_gunung') is-invalid @enderror">
                                <option value="" disabled>Pilih Gunung</option>
                                @foreach($pegunungan as $gunung)
                                    <option value="{{ $gunung->id }}" {{ $gunung->id == $jalur->id_gunung ? 'selected' : '' }}>
                                        {{ $gunung->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_gunung')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Input Nama Jalur -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Nama Jalur</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $jalur->nama) }}" placeholder="Masukkan Nama Jalur">
                            @error('nama')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Dropdown Provinsi -->
                        <div class="form-group mb-3">
                            <label for="province_id" class="font-weight-bold">Provinsi</label>
                            <select id="province_id" name="province_id" class="form-control @error('province_id') is-invalid @enderror">
                                <option value="" disabled>Pilih Provinsi</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}" {{ $province->id == $jalur->province_id ? 'selected' : '' }}>
                                        {{ $province->name }}
                                    </option>
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
                                <option value="" disabled>Pilih Kabupaten</option>
                                @foreach($regencies as $regency)
                                    <option value="{{ $regency->id }}" {{ $regency->id == $jalur->regency_id ? 'selected' : '' }}>
                                        {{ $regency->name }}
                                    </option>
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
                                <option value="" disabled>Pilih Kecamatan</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}" {{ $district->id == $jalur->district_id ? 'selected' : '' }}>
                                        {{ $district->name }}
                                    </option>
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
                                <option value="" disabled>Pilih Desa</option>
                                @foreach($villages as $village)
                                    <option value="{{ $village->id }}" {{ $village->id == $jalur->village_id ? 'selected' : '' }}>
                                        {{ $village->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('village_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Input Jarak -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Jarak</label>
                            <input type="text" name="jarak" class="form-control @error('jarak') is-invalid @enderror" value="{{ old('jarak', $jalur->jarak) }}" placeholder="Masukkan Jarak Jalur">
                            @error('jarak')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Input Deskripsi -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="5" placeholder="Masukkan Deskripsi Jalur">{{ old('deskripsi', $jalur->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Input Map Basecamp -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Map Basecamp</label>
                            <input type="text" name="map_basecamp" class="form-control @error('map_basecamp') is-invalid @enderror" value="{{ old('map_basecamp', $jalur->map_basecamp) }}" placeholder="Masukkan Link Map Basecamp">
                            @error('map_basecamp')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="gambar_jalur">Upload Gambar Jalur</label>
                            <input type="file" class="form-control @error('gambar_jalur') is-invalid @enderror" id="gambar_jalur" name="gambar_jalur" >
                            @error('gambar_jalur')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Input Biaya -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Biaya</label>
                            <input type="text" name="biaya" class="form-control @error('biaya') is-invalid @enderror" value="{{ old('biaya', $jalur->biaya) }}" placeholder="Masukkan Biaya Pendakian">
                            @error('biaya')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Tombol Simpan dan Batal -->
                        <div class="d-flex justify-content-start">
                            <button type="submit" class="btn" style="background-color: #117958; color: white; border: none; margin-right: 10px;">Simpan</button>
                            <a href="{{ route('jalur.index') }}" class="btn btn-danger">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
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
