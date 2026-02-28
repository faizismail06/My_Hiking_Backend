@extends('layouts.admin-modern')

@section('page-title', 'Tata Tertib')
@section('page-subtitle', 'Kelola tata tertib untuk setiap jalur pendakian')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-clipboard-list"></i> Data Tata Tertib</h5>
        <a href="{{ route('rules.create') }}" class="btn btn-modern btn-primary-modern">
            <i class="fas fa-plus"></i> Tambah Tata Tertib
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Jalur</th>
                        <th>Deskripsi</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rules as $rule)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <span class="modern-badge badge-success">{{ $rule->jalur->nama ?? '-' }}</span>
                        </td>
                        <td>{{ $rule->description }}</td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('rules.edit', $rule) }}" class="btn btn-sm btn-modern btn-warning-modern" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form onsubmit="return confirm('Yakin ingin menghapus tata tertib ini?');" action="{{ route('rules.destroy', $rule) }}" method="POST" class="d-inline">
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
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="fas fa-clipboard-list fa-2x mb-2 d-block"></i>
                            Belum ada data tata tertib
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
