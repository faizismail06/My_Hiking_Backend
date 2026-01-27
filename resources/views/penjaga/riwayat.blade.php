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
                <h1 class="h3 mb-0 text-gray-800">Riwayat Pengunjung</h1>
                <a href="{{ route('penjaga.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
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

            <!-- Scanner QR Code -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Scan QR Code Pesanan</h6>
                </div>
                <div class="card-body">
                    <div id="reader" style="width: 100%; max-width: 600px; margin: auto;"></div>
                </div>
            </div>

            <!-- Filter -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Pencarian</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('penjaga.riwayat') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Cari ID pesanan..."
                                    value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-control">
                                    <option value="">-- Semua Status --</option>
                                    <option value="Booking" {{ request('status') == 'Booking' ? 'selected' : '' }}>Booking
                                    </option>
                                    <option value="Sedang Mendaki"
                                        {{ request('status') == 'Sedang Mendaki' ? 'selected' : '' }}>Sedang Mendaki
                                    </option>
                                    <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Riwayat -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Daftar Pengunjung - {{ $jalur->nama }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead style="background-color: #d4edda;">
                                <tr>
                                    <th class="text-center">ID Pesanan</th>
                                    <th class="text-center">Ketua</th>
                                    <th class="text-center">Tanggal Naik</th>
                                    <th class="text-center">Tanggal Turun</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pesanan as $p)
                                    <tr>
                                        <td class="text-center">{{ $p->id }}</td>
                                        <td class="text-center">
                                            {{ $p->user ? $p->user->name : 'Tidak Diketahui' }}
                                        </td>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($p->tanggal_naik)->format('d M Y') }}</td>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($p->tanggal_turun)->format('d M Y') }}</td>
                                        <td class="text-center">
                                            @if ($p->status == 'Booking')
                                                <span style="font-weight: bold; color: orange;">Booking</span>
                                            @elseif($p->status == 'Sedang Mendaki')
                                                <span style="font-weight: bold; color: green;">Mendaki</span>
                                            @elseif($p->status == 'Selesai')
                                                <span style="font-weight: bold; color: black;">Selesai</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('penjaga.scanner.detail', $p->id) }}"
                                                class="btn btn-sm btn-info">DETAIL</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada data pengunjung</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $pesanan->links() }}
                    </div>
                </div>
            </div>
        </div>

        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
        <script>
            function onScanSuccess(decodedText, decodedResult) {
                console.log(`Code matched = ${decodedText}`, decodedResult);

                // Redirect ke halaman detail dengan ID yang di-scan
                window.location.href = `/penjaga/scanner/detail/${decodedText}`;
            }

            function onScanFailure(error) {
                // Silent error
            }

            let html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                },
                false
            );
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        </script>
    </body>
@endsection
