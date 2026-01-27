@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard Penjaga Jalur</h1>
            <div class="text-gray-600">
                <i class="fas fa-user-shield"></i> {{ Auth::user()->name }}
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Info Jalur -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success text-white">
                <h6 class="m-0 font-weight-bold">Informasi Jalur yang Dikelola</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="font-weight-bold text-primary">{{ $jalur->nama }}</h5>
                        <p class="mb-1"><strong>Gunung:</strong> {{ $jalur->gunung->nama }}</p>
                        <p class="mb-1"><strong>Lokasi:</strong> {{ $jalur->village->name }},
                            {{ $jalur->district->name }}, {{ $jalur->regency->name }}</p>
                        <p class="mb-1"><strong>Jarak:</strong> {{ $jalur->jarak }} km</p>
                        <p class="mb-1"><strong>Biaya:</strong> Rp {{ number_format($jalur->biaya, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-6 text-right">
                        @if ($jalur->gambar_jalur)
                            <img src="{{ asset('storage/images/' . $jalur->gambar_jalur) }}" alt="{{ $jalur->nama }}"
                                class="img-thumbnail" style="max-height: 200px;">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Cards -->
        <div class="row">
            <!-- Pengunjung Hari Ini -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Pengunjung Hari Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pengunjungHariIni }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pengunjung Bulan Ini -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Bulan Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pengunjungBulanIni }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pendapatan Bulan Ini -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Pendapatan Bulan Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Rp
                                    {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Menu Cepat</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('penjaga.scanner') }}" class="btn btn-warning btn-block">
                                    <i class="fas fa-qrcode"></i> Scanner Check In/Out
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('penjaga.jalur') }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-route"></i> Kelola Jalur
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('penjaga.riwayat') }}" class="btn btn-success btn-block">
                                    <i class="fas fa-history"></i> Riwayat Pengunjung
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('penjaga.pendapatan') }}" class="btn btn-info btn-block">
                                    <i class="fas fa-chart-line"></i> Laporan Pendapatan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pesanan Terbaru -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Pesanan Terbaru</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Pendaki</th>
                                <th>Jumlah Anggota</th>
                                <th>Tanggal Naik</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pesananTerbaru as $pesanan)
                                <tr>
                                    <td>{{ $pesanan->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $pesanan->user->name }}</td>
                                    <td>{{ $pesanan->anggota->count() }} orang</td>
                                    <td>{{ \Carbon\Carbon::parse($pesanan->tanggal_naik)->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($pesanan->status == 'Menunggu Konfirmasi')
                                            <span class="badge badge-warning">{{ $pesanan->status }}</span>
                                        @elseif($pesanan->status == 'Dikonfirmasi')
                                            <span class="badge badge-success">{{ $pesanan->status }}</span>
                                        @elseif($pesanan->status == 'Sedang Mendaki')
                                            <span class="badge badge-primary">{{ $pesanan->status }}</span>
                                        @elseif($pesanan->status == 'Selesai')
                                            <span class="badge badge-secondary">{{ $pesanan->status }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ $pesanan->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($pesanan->status == 'Dikonfirmasi')
                                            <form action="{{ route('penjaga.checkin', $pesanan->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Check In</button>
                                            </form>
                                        @elseif($pesanan->status == 'Sedang Mendaki')
                                            <form action="{{ route('penjaga.checkout', $pesanan->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">Check Out</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada pesanan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
