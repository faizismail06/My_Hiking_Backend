@extends('layouts.admin-modern')

@section('page-title', 'Daftar Jalur')
@section('page-subtitle', 'Kelola semua jalur pendakian')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5><i class="fas fa-route"></i> Data Jalur</h5>
        <div class="d-flex gap-2">
            <form action="{{ route('trails.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Cari jalur..." value="{{ request()->get('search') }}" style="width: 200px;">
                <button type="submit" class="btn btn-modern btn-outline-modern">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <a href="{{ route('trails.create') }}" class="btn btn-modern btn-primary-modern">
                <i class="fas fa-plus"></i> Tambah Jalur
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Nama Jalur</th>
                        <th>Gunung</th>
                        <th>Lokasi</th>
                        <th class="text-center">Jarak</th>
                        <th class="text-center">Biaya</th>
                        <th class="text-center">Gambar</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trails as $j)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold">{{ $j->nama }}</div>
                            <div class="small text-muted">{{ Str::limit($j->deskripsi, 50) }}</div>
                        </td>
                        <td>
                            <span class="modern-badge badge-success">{{ $j->gunung->nama ?? '-' }}</span>
                        </td>
                        <td>
                            <div class="small">
                                <i class="fas fa-map-marker-alt me-1 text-muted"></i>
                                {{ $j->province->name ?? '-' }}, {{ $j->regency->name ?? '-' }}
                            </div>
                            <div class="small text-muted">{{ $j->district->name ?? '-' }}, {{ $j->village->name ?? '-' }}</div>
                        </td>
                        <td class="text-center">
                            <span class="modern-badge badge-info">{{ rtrim(rtrim(number_format($j->jarak, 2, '.', ''), '0'), '.') }} km</span>
                        </td>
                        <td class="text-center">
                            <span class="modern-badge badge-warning">Rp {{ number_format($j->biaya, 0, ',', '.') }}</span>
                        </td>
                        <td class="text-center">
                            @if ($j->gambar_jalur)
                                <img src="{{ asset('/storage/images/' . $j->gambar_jalur) }}" alt="Gambar" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('trails.edit', $j->id) }}#map-editor" class="btn btn-sm btn-modern btn-outline-modern" title="Lihat Peta Jalur">
                                    <i class="fas fa-map-marked-alt"></i>
                                </a>
                                <a href="{{ route('trails.edit', $j->id) }}" class="btn btn-sm btn-modern btn-warning-modern" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form onsubmit="return confirm('Yakin ingin menghapus jalur ini?');" action="{{ route('trails.destroy', $j->id) }}" method="POST" class="d-inline">
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
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="fas fa-route fa-2x mb-2 d-block"></i>
                            Tidak ada data jalur
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
