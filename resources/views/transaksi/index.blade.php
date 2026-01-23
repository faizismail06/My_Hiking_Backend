@extends('layouts.admin')
@section('main-content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body style="background: #117958;">
    <div class="container bg-white p-4 rounded">
        <h1 class="text-center my-4" style="font-weight: bold; color: black;">Daftar Transaksi</h1>
        <!-- Baris untuk form pencarian -->
        <div class="d-flex justify-content-end mb-3">
            <form action="{{ route('transaksi.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control" placeholder="Cari transaksi..." value="{{ request()->get('search') }}">
                <button type="submit" class="btn btn-primary ms-2" style="background-color: #117958; border: none;">Cari</button>
            </form>
        </div>
        
        <!-- Pesan sukses -->
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Tabel transaksi -->
        <table class="table table-bordered">
            <thead style="background-color: #d4edda;">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">ID Pesanan</th>
                    <th class="text-center">Metode Pembayaran</th>
                    <th class="text-center">Total Bayar</th>
                    <th class="text-center">Status Pesanan</th>
                    <th class="text-center" style="width: 20%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaksis as $key => $transaksi)
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td class="text-center">{{ $transaksi->id_pesanan }}</td>
                    <td class="text-center">{{ $transaksi->payment->nama_pembayaran ?? 'Metode tidak ditemukan' }}</td>
                    <td class="text-center">{{ number_format($transaksi->total_bayar, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if ($transaksi->status_pesanan === 'Unverified')
                            <span class="text-danger">Unverified</span>
                        @elseif ($transaksi->status_pesanan === 'Verified')
                            <span class="text-success">Verified</span>
                        @elseif ($transaksi->status_pesanan === 'Incomplete')
                            <span class="text-warning">Incomplete</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <a href="{{ route('transaksi.show', $transaksi->id) }}" class="btn btn-sm btn-dark">DETAIL</a>
                        
                        <!-- Tombol Verifikasi -->
                        @if ($transaksi->status_pesanan === 'Unverified') <!-- Menampilkan tombol verifikasi jika status 'unverified' -->
                            <form action="{{ route('transaksi.verify', $transaksi->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm" style="background-color: #117958; color: white; border: none;">VERIFIKASI</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
@endsection
