@extends('layouts.app')

@section('content')
<body style="background: #117958;">
    <div class="container bg-white p-4 rounded position-relative">
        <h1 class="text-center mb-4" style="font-weight: bold; color: black;">Rincian Transaksi</h1>
        <table class="table table-bordered">
            <tr>
                <th style="width: 30%;">ID Transaksi</th>
                <td>{{ $transaksi->id }}</td>
            </tr>
            <tr>
                <th>ID Pesanan</th>
                <td>{{ $transaksi->id_pesanan }}</td>
            </tr>
            <tr>
                <th>Metode Pembayaran</th>
                <td>{{ $transaksi->payment->nama_pembayaran ?? 'Metode tidak ditemukan' }}</td>
            </tr>
            <tr>
                <th>Total Bayar</th>
                <td>{{ number_format($transaksi->total_bayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Status Pesanan</th>
                <td>
                    @if ($transaksi->status_pesanan === 'Verified')
                        <span class="text-success">Verified</span>
                    @elseif ($transaksi->status_pesanan === 'Unverified')
                        <span class="text-danger">Unverified</span>
                    @else
                        <span class="text-warning">Incomplete</span>
                    @endif
                </td>
            </tr>

            <tr>
                <th>Waktu Pembayaran</th>
                <td>{{ $transaksi->waktu_pembayaran ? \Carbon\Carbon::parse($transaksi->waktu_pembayaran)->format('d-m-Y H:i:s') : 'Belum dibayar' }}</td>
            </tr>
            <tr>
                <th>Bukti Pembayaran</th>
                <td class="text-center">
                    @if ($transaksi->bukti)
                        <img src="{{ asset('/storage/' . $transaksi->bukti) }}" alt="Bukti Pembayaran" class="img-fluid" style="max-width: 300px;">
                    @else
                        <p class="text-danger">Tidak ada bukti pembayaran.</p>
                    @endif
                </td>
            </tr>
        </table>
        <div class="d-flex justify-content-center mt-4">
            <a href="{{ route('transaksi.index') }}" class="btn btn-primary" style="background-color: #117958; border: none;">Kembali ke Daftar Transaksi</a>
        </div>
    </div>
</body>
@endsection
