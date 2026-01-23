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
                    <h1 class="text-start my-4" style="font-weight: bold; color: black;">Tambah Pembayaran</h1>
                    <form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Input ID Pembayaran -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">ID Pembayaran</label>
                            <input type="text" name="id" class="form-control @error('id') is-invalid @enderror" value="{{ old('id') }}" placeholder="Masukkan ID Pembayaran">
                            @error('id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Input Nama Pembayaran -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Nama Pembayaran</label>
                            <input type="text" name="nama_pembayaran" class="form-control @error('payment_name') is-invalid @enderror" value="{{ old('payment_name') }}" placeholder="Masukkan Nama Pembayaran">
                            @error('payment_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Input Nomor Pembayaran -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Nomor Pembayaran</label>
                            <input type="text" name="nomor_pembayaran" class="form-control @error('payment_number') is-invalid @enderror" value="{{ old('payment_number') }}" placeholder="Masukkan Nomor Pembayaran">
                            @error('payment_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Upload Gambar Pembayaran -->
                        <div class="form-group mb-3">
                            <label for="gambar_pembayaran" class="font-weight-bold">Upload Logo Pembayaran</label>
                            <input type="file" class="form-control @error('gambar_pembayaran') is-invalid @enderror" id="gambar_pembayaran" name="gambar_pembayaran">
                            @error('gambar_pembayaran')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Tombol Simpan dan Batal -->
                        <div class="d-flex justify-content-start">
                            <button type="submit" class="btn" style="background-color: #117958; color: white; border: none; margin-right: 10px;">Simpan</button>
                            <a href="{{ route('payments.index') }}" class="btn btn-danger">Batal</a>
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
        // Implementasikan logika yang dibutuhkan jika perlu
    });
</script>
@endsection
