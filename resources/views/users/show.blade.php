@extends('layouts.app')

@section('content')
<div class="container bg-white p-4 rounded">
    <h1 class="text-center mb-4" style="font-weight: bold; color: black;">Detail Pengguna</h1>
    <table class="table table-bordered">
        <tr>
            <th style="width: 30%;">ID</th>
            <td>{{ $user->id }}</td>
        </tr>
        <tr>
            <th>Nama</th>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <th>Alamat</th>
            <td>{{ $user->address }}</td>
        </tr>
        <tr>
            <th>NIK</th>
            <td>{{ $user->nik }}</td>
        </tr>
        <tr>
            <th>No. Telepon</th>
            <td>{{ $user->phone }}</td>
        </tr>
        <tr>
            <th>No. Telepon Darurat</th>
            <td>{{ $user->emergency_phone }}</td>
        </tr>
        <tr>
            <th>Tanggal Lahir</th>
            <td>{{ $user->date_of_birth }}</td>
        </tr>
        <tr>
            <th>Foto Profil</th>
            <td class="text-center">
                @if ($user->profile_picture)
                    <img src="{{ asset('/storage/' . $user->profile_picture) }}" alt="Foto Profil" class="img-fluid"
                        style="max-width: 300px; border-radius: 5px;">
                @else
                    <p class="text-danger">Tidak ada foto profil.</p>
                @endif
            </td>
        </tr>
    </table>
    <div class="d-flex justify-content-center mt-4">
        <a href="{{ route('users.index') }}" class="btn btn-primary"
            style="background-color: #117958; border: none;">Kembali ke Daftar Pengguna</a>
        <!-- Delete button -->
        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"
                onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">Hapus</button>
        </form>
    </div>
</div>
@endsection