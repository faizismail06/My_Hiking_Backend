@extends('layouts.admin')
@section('main-content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body style="background: #117958;">
    <div class="container bg-white p-4 rounded">
        <h1 class="text-center my-4" style="font-weight: bold; color: black;">Daftar Pengguna</h1>

        <!-- Baris untuk form pencarian atau aksi lainnya -->
        <div class="d-flex justify-content-end mb-3">
            <!-- Form Pencarian -->
            <form action="{{ route('users.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control" placeholder="Cari pengguna..." value="{{ request()->get('search') }}">
                <button type="submit" class="btn btn-primary ms-2" style="background-color:  #117958; border: none;">Cari</button>
            </form>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead style="background-color: #d4edda;"> <!-- Header tabel -->
                <tr>
                    <th class="text-center">ID User</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Email</th>
                    <th class="text-center" style="width: 20%;">Aksi</th> <!-- Kolom aksi -->
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr>
                    <td class="text-center">{{ $user->id }}</td>
                    <td class="text-center">{{ $user->name }}</td>
                    <td class="text-center">{{ $user->email }}</td>
                    <td class="text-center">
                        <!-- Tombol Detail -->
                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-dark">LIHAT DETAIL</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
@endsection
