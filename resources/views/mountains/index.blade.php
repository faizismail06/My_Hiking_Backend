@extends('layouts.admin-modern')

@section('page-title', 'Daftar Gunung')
@section('page-subtitle', 'Kelola semua data gunung yang terdaftar')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-mountain"></i> Data Gunung</h5>
        <div class="d-flex gap-2">
            <form action="{{ route('mountains.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Cari gunung..." value="{{ request()->get('search') }}" style="width: 200px;">
                <button type="submit" class="btn btn-modern btn-outline-modern">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <a href="{{ route('mountains.create') }}" class="btn btn-modern btn-primary-modern">
                <i class="fas fa-plus"></i> Tambah Gunung
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Nama Gunung</th>
                        <th>Lokasi</th>
                        <th class="text-center">Ketinggian</th>
                        <th class="text-center">Gambar</th>
                        <th class="text-center" style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mountains as $mountain)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold">{{ $mountain->nama }}</div>
                        </td>
                        <td>
                            <div class="small text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                {{ $mountain->province->name ?? '-' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="modern-badge badge-info">{{ $mountain->ketinggian ?? '-' }} mdpl</span>
                        </td>
                        <td class="text-center">
                            @if ($mountain->gambar_gunung)
                                <img src="{{ asset('/storage/images/' . $mountain->gambar_gunung) }}" class="rounded" style="width: 80px; height: 50px; object-fit: cover;">
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('mountains.show', $mountain->id) }}" class="btn btn-sm btn-modern btn-outline-modern" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('mountains.edit', $mountain->id) }}" class="btn btn-sm btn-modern btn-warning-modern" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form onsubmit="return confirm('Yakin ingin menghapus gunung ini?');" action="{{ route('mountains.destroy', $mountain->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-modern btn-danger-modern" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-mountain fa-2x mb-2 d-block"></i>
                            Tidak ada data gunung
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
