@extends('layouts.app')

@section('content')

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>

    <body style="background: #117958;">
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Detail Pesanan</h1>
                <a href="{{ route('penjaga.scanner') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Scanner
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
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <!-- Status Pesanan -->
                <div class="col-lg-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold">Status Pesanan</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Status Saat Ini:
                                        @if ($pesanan->status == 'Booking')
                                            <span class="badge badge-warning">{{ $pesanan->status }}</span>
                                        @elseif($pesanan->status == 'Sedang Mendaki')
                                            <span class="badge badge-info">{{ $pesanan->status }}</span>
                                        @elseif($pesanan->status == 'Selesai')
                                            <span class="badge badge-success">{{ $pesanan->status }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $pesanan->status }}</span>
                                        @endif
                                    </h5>
                                </div>
                                <div class="col-md-6 text-right">
                                    @if ($pesanan->status == 'Booking')
                                        <form action="{{ route('penjaga.updateStatus', $pesanan->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="Sedang Mendaki">
                                            <button type="submit" class="btn btn-success btn-lg"
                                                onclick="return confirm('Konfirmasi Check-In pendaki?')">
                                                <i class="fas fa-sign-in-alt"></i> CHECK IN
                                            </button>
                                        </form>
                                    @elseif($pesanan->status == 'Sedang Mendaki')
                                        <form action="{{ route('penjaga.updateStatus', $pesanan->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="Selesai">
                                            <button type="submit" class="btn btn-primary btn-lg"
                                                onclick="return confirm('Konfirmasi Check-Out pendaki?')">
                                                <i class="fas fa-sign-out-alt"></i> CHECK OUT
                                            </button>
                                        </form>
                                    @elseif($pesanan->status == 'Selesai')
                                        <button class="btn btn-secondary btn-lg" disabled>
                                            <i class="fas fa-check-circle"></i> Sudah Selesai
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Pesanan -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-info text-white">
                            <h6 class="m-0 font-weight-bold">Informasi Pesanan</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">ID Pesanan</th>
                                    <td>: {{ $pesanan->id }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Booking</th>
                                    <td>: {{ \Carbon\Carbon::parse($pesanan->tanggal_booking)->isoFormat('D MMMM Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tanggal Naik</th>
                                    <td>: {{ \Carbon\Carbon::parse($pesanan->tanggal_naik)->isoFormat('D MMMM Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Lama Pendakian</th>
                                    <td>: {{ $pesanan->lama_hari }} hari</td>
                                </tr>
                                <tr>
                                    <th>Gunung</th>
                                    <td>: {{ $pesanan->jalur->gunung->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Jalur</th>
                                    <td>: {{ $pesanan->jalur->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Pendaki</th>
                                    <td>: {{ $pesanan->anggotaPesanan->count() }} orang</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Informasi Pembayaran -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-success text-white">
                            <h6 class="m-0 font-weight-bold">Informasi Pembayaran</h6>
                        </div>
                        <div class="card-body">
                            @if ($pesanan->transaksi)
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Total Bayar</th>
                                        <td>: Rp {{ number_format($pesanan->transaksi->total_bayar, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Metode Pembayaran</th>
                                        <td>: {{ $pesanan->transaksi->payment->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status Pembayaran</th>
                                        <td>:
                                            @if ($pesanan->transaksi->status_pesanan == 'Verified')
                                                <span class="badge badge-success">Lunas</span>
                                            @else
                                                <span
                                                    class="badge badge-warning">{{ $pesanan->transaksi->status_pesanan }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Bayar</th>
                                        <td>:
                                            {{ \Carbon\Carbon::parse($pesanan->transaksi->created_at)->isoFormat('D MMMM Y HH:mm') }}
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <div class="alert alert-warning">
                                    Belum ada informasi pembayaran
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Daftar Anggota -->
                <div class="col-lg-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-warning text-white">
                            <h6 class="m-0 font-weight-bold">Daftar Anggota Pendaki</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>No</th>
                                            <th>NIK</th>
                                            <th>Nama</th>
                                            <th>No HP</th>
                                            <th>Alamat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pesanan->anggotaPesanan as $index => $anggota)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $anggota->nik }}</td>
                                                <td>{{ $anggota->nama }}</td>
                                                <td>{{ $anggota->no_hp }}</td>
                                                <td>{{ $anggota->alamat }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data anggota</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
@endsection
