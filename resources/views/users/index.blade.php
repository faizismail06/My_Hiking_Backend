@extends('layouts.admin-modern')

@section('page-title', 'Daftar Pengguna')
@section('page-subtitle', 'Kelola data pengguna aplikasi')

@section('main-content')
    <div class="modern-card animate-fade-in">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-users"></i> Data Pengguna</h5>
            <div class="d-flex gap-2">
                <form action="{{ route('users.index') }}" method="GET" class="d-flex gap-2">
                    <select name="level" class="form-select" style="width: 150px;" onchange="this.form.submit();">
                        <option value="">Semua Level</option>
                        <option value="1" @if (request()->get('level') == 1) selected @endif>User</option>
                        <option value="2" @if (request()->get('level') == 2) selected @endif>Penjaga Jalur</option>
                        <option value="3" @if (request()->get('level') == 3) selected @endif>Admin</option>
                    </select>
                    <input type="text" name="search" class="form-control" placeholder="Cari pengguna..."
                        value="{{ request()->get('search') }}" style="width: 200px;">
                    <button type="submit" class="btn btn-modern btn-outline-modern">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <a href="{{ route('users.create') }}" class="btn btn-modern btn-success">
                    <i class="fas fa-plus"></i> Tambah
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Pengguna</th>
                            <th>Email</th>
                            <th class="text-center">Level</th>
                            @if ($levelFilter == 2)
                                <th>Gunung</th>
                                <th>Jalur</th>
                            @endif
                            <th class="text-center" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm"
                                            style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <span
                                                style="color: white; font-size: 14px; font-weight: 600;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $user->email }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($user->level == 1)
                                        <span class="badge bg-info">User</span>
                                    @elseif($user->level == 2)
                                        <span class="badge bg-warning">Penjaga</span>
                                    @else
                                        <span class="badge bg-danger">Admin</span>
                                    @endif
                                </td>
                                @if ($levelFilter == 2)
                                    <td>
                                        @if ($user->trails && $user->trails->count() > 0)
                                            <div class="d-flex flex-column gap-1">
                                                @foreach ($user->trails->unique('id_gunung') as $trail)
                                                    <span
                                                        class="badge bg-info">{{ $trail->mountain ? $trail->mountain->nama : 'N/A' }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($user->trails && $user->trails->count() > 0)
                                            <div class="d-flex flex-column gap-1">
                                                @foreach ($user->trails as $trail)
                                                    <span class="badge bg-success">{{ $trail->nama }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('users.show', $user->id) }}"
                                            class="btn btn-sm btn-modern btn-outline-modern" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $levelFilter == 2 ? 7 : 5 }}" class="text-center py-4 text-muted">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    Tidak ada data pengguna
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
