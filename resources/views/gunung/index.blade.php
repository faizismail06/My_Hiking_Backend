@extends('layouts.admin')
@section('main-content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body style="background: #117958">
    <div class="container bg-white p-4 rounded">
                    <h1 class="text-center my-4" style="font-weight: bold; color: black;">Daftar Gunung</h1> <!-- Gaya font disamakan -->
                    <!-- Baris untuk tombol "Tambah Gunung" dan form pencarian -->
                    <div class="d-flex justify-content-between mb-3">
                    <a href="{{ route('gunung.create') }}" class="btn" style="background-color: #FFA500; color: white; border: none;">
                        <i class="fas fa-plus"></i> Tambah Gunung
                    </a>

                        <!-- Form Pencarian -->
                        <form action="{{ route('gunung.index') }}" method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" placeholder="Cari gunung..." value="{{ request()->get('search') }}">
                            <button type="submit" class="btn btn-success" style="background-color: #117958; border: none;">Cari</button>
                        </form>

                    </div>
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <table class="table table-bordered">
                        <thead style="background-color: #d4edda;"> <!-- Gaya tabel disamakan -->
                            <tr>
                                <th scope="col" class="text-center">ID</th>
                                <th scope="col" class="text-center">Nama Gunung</th>
                                <th scope="col" class="text-center">Provinsi</th>
                                <th scope="col" class="text-center">Kabupaten</th>
                                <th scope="col" class="text-center">Kecamatan</th>
                                <th scope="col" class="text-center">Desa</th>
                                <th scope="col" class="text-center">Ketinggian</th>
                                <th scope="col" class="text-center">Gambar</th>
                                <th scope="col" class="text-center" style="width: 20%">Aksi</th>
                            </tr>
                        </thead>                        
                        <tbody>
                            @foreach($gunungs as $gunung)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td style=>{{ $gunung->nama }}</td> <!-- Warna teks hijau disamakan -->
                                <td>{{ $gunung->province->name ?? 'Tidak Diketahui' }}</td> <!-- Menampilkan Nama Provinsi -->
                                <td>{{ $gunung->regency->name ?? 'Tidak Diketahui' }}</td> <!-- Menampilkan Nama Kabupaten -->
                                <td>{{ $gunung->district->name ?? 'Tidak Diketahui' }}</td> <!-- Menampilkan Nama Kecamatan -->
                                <td>{{ $gunung->village->name ?? 'Tidak Diketahui' }}</td> <!-- Menampilkan Nama Desa -->
                                <td>{{ $gunung->ketinggian ?? 'Tidak Diketahui' }} mdpl</td>
                                <td class="text-center">
                                    @if ($gunung->gambar_gunung)
                                    <img src="{{ asset('/storage/images/' . $gunung->gambar_gunung) }}" class="rounded" style="width: 150px">
                                    @else
                                        <span>Tidak Ada Gambar</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form onsubmit="return confirm('Yakin ingin menghapus?');" action="{{ route('gunung.destroy', $gunung->id) }}" method="POST">
                                        <a href="{{ route('gunung.show', $gunung->id) }}" class="btn btn-sm btn-dark">DETAIL</a>
                                        <a href="{{ route('gunung.edit', $gunung->id) }}" class="btn btn-sm" style="background-color: #28a745; color: white; border: none;">EDIT</a>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">HAPUS</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
