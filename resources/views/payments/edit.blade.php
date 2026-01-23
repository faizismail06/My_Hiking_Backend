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
                    <h1 class="text-start my-4" style="font-weight: bold; color: black;">Edit Pembayaran</h1>
                    <form action="{{ route('payments.update', $payment->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Input ID Pembayaran (tidak perlu diedit, hanya ditampilkan) -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">ID Pembayaran</label>
                            <input type="text" name="id" class="form-control" value="{{ $payment->id }}" readonly>
                        </div>

                        <!-- Input Nama Pembayaran -->
                        <div class="form-group mb-3">
                            <label for="nama_pembayaran" class="font-weight-bold">Nama Pembayaran</label>
                            <input type="text" name="nama_pembayaran" class="form-control @error('nama_pembayaran') is-invalid @enderror" value="{{ old('nama_pembayaran', $payment->nama_pembayaran) }}">
                            @error('nama_pembayaran')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Input Nomor Pembayaran -->
                        <div class="form-group mb-3">
                            <label for="nomor_pembayaran" class="font-weight-bold">Nomor Pembayaran</label>
                            <input type="text" name="nomor_pembayaran" class="form-control @error('nomor_pembayaran') is-invalid @enderror" value="{{ old('nomor_pembayaran', $payment->nomor_pembayaran) }}">
                            @error('nomor_pembayaran')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Input Gambar Pembayaran -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Gambar Pembayaran</label>
                            <input type="file" name="gambar_pembayaran" class="form-control @error('gambar_pembayaran') is-invalid @enderror">
                            @if ($payment->gambar_pembayaran)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $payment->gambar_pembayaran) }}" alt="Gambar Pembayaran" style="max-width: 150px;">
                                    <p class="mt-2 text-muted">Gambar saat ini</p>
                                </div>
                            @endif
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
@endsection
