@extends('layouts.admin')

@section('main-content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body style="background: #117958;">
    <div class="container bg-white p-4 rounded">
        <h1 class="text-center my-4" style="font-weight: bold; color: black;">Daftar Pembayaran</h1>
        
        <!-- Tombol tambah dan pencarian -->
        <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('payments.create') }}" class="btn" style="background-color: #FFA500; color: white; border: none;">
            <i class="fas fa-plus"></i> Tambah Pembayaran
        </a>

            <!-- Form Pencarian -->
            <form action="{{ route('payments.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control" placeholder="Cari pembayaran..." value="{{ request()->get('search') }}">
                <button type="submit" class="btn btn-primary ms-2" style="background-color:  #117958; border: none;">Cari</button>
            </form>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Tabel Pembayaran -->
        <table class="table table-bordered">
            <thead style="background-color: #d4edda;">
                <tr>
                    <th class="text-center">ID Pembayaran</th>
                    <th class="text-center">Nama Pembayaran</th>
                    <th class="text-center">Nomor Pembayaran</th>
                    <th class="text-center">Logo</th>
                    <th class="text-center" style="width: 15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                    <tr>
                        <td class="text-center">{{ $payment->id }}</td>
                        <td class="text-center">{{ $payment->nama_pembayaran }}</td>
                        <td class="text-center">{{ $payment->nomor_pembayaran }}</td>
                        <td class="text-center">
                            @if ($payment->gambar_pembayaran)
                                <img src="{{ asset('storage/' . $payment->gambar_pembayaran) }}" alt="Gambar Pembayaran" style="max-width: 100px;">
                            @else
                                Tidak ada gambar
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('payments.edit', $payment->id) }}"  class="btn btn-sm" style="background-color: #28a745; color: white; margin-right: 5px;">EDIT</a>
                            <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" style="margin-left: 5px;">HAPUS</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $payments->links() }}
        </div>
    </div>
</body>
@endsection
