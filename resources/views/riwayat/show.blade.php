@extends('layouts.app')

@section('content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Detail Pesanan</title>
</head>
<body style="background: #117958">
    <div class="container bg-white p-4 rounded position-relative">

        <!-- Tombol Back Baru (Simbol Panah) dengan Background Kotak dan Sudut Tumpul -->
        <a href="{{ route('riwayat.index') }}" 
           class="btn btn-sm position-absolute" 
           style="top: 15px; left: 15px; color: #fff; font-weight: bold; font-size: 24px; display: flex; align-items: center; 
                  background-color: #FFA500; padding: 10px 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <svg xmlns="http://www.w3.org/2000/svg" 
                 width="30" height="30" 
                 fill="currentColor" 
                 class="bi bi-arrow-left" 
                 viewBox="0 0 16 16" 
                 style="font-weight: extra-bold;">
                <path fill-rule="evenodd" 
                      d="M15 8a.5.5 0 0 1-.5.5H3.707l3.147 3.146a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L3.707 7.5H14.5A.5.5 0 0 1 15 8z"/>
            </svg>
        </a>

        <h1 class="text-center my-4" style="font-weight: bold; color: black; position: relative; padding-left: 40px;">
            Detail Pesanan
        </h1>

        <!-- Detail Pesanan -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card-body">
                    <h3 class="text-left mb-4">Pesanan #{{ $pesanan->id }}</h3>
                    <table class="table table-bordered mx-auto">
                        <tr>
                            <th>Ketua</th>
                            <td>{{ $pesanan->user->name ?? 'Tidak Diketahui' }}</td>
                        </tr>
                        <tr>
                            <th>Anggota</th>
                            <td>
                                @foreach ($pesanan->anggotaPesanan as $anggota)
                                    <li>{{ $anggota->user->name ?? 'Tidak Diketahui' }}</li>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>Gunung</th>
                            <td>{{ $pesanan->gunung->nama ?? 'Tidak Diketahui' }}</td>
                        </tr>
                        <tr>
                            <th>Jalur</th>
                            <td>{{ $pesanan->jalur->nama ?? 'Tidak Diketahui' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Naik</th>
                            <td>{{ $pesanan->tanggal_naik->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Turun</th>
                            <td>{{ $pesanan->tanggal_turun->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th>Total Harga Tiket</th>
                            <td>Rp {{ number_format($pesanan->total_harga_tiket, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($pesanan->status == 'Booking')
                                    <span style="font-weight: bold; color: orange;">Booking</span>
                                @elseif($pesanan->status == 'Sedang Mendaki')
                                    <span style="font-weight: bold; color: green;">Mendaki</span>
                                @elseif($pesanan->status == 'Selesai')
                                    <span style="font-weight: bold; color: black;">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    <!-- Tombol Ganti Status -->
                    @if($pesanan->status != 'Selesai')
                    <form action="{{ route('riwayat.updateStatus', $pesanan->id) }}" method="POST" class="mt-4">
                        @csrf
                        @method('PUT')

                        @if($pesanan->status == 'Booking')
                            <button type="submit" name="status" value="Sedang Mendaki" class="btn" style="background-color: orange; color: white;">Ganti ke Mendaki</button>
                        @elseif($pesanan->status == 'Sedang Mendaki')
                            <button type="submit" name="status" value="Selesai" class="btn" style="background-color: orange; color: white;">Ganti ke Selesai</button>
                        @endif
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
@endsection
