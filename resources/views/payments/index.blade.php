@extends('layouts.admin-modern')

@section('page-title', 'Metode Pembayaran')
@section('page-subtitle', 'Kelola metode pembayaran yang tersedia')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-credit-card"></i> Data Pembayaran</h5>
        <div class="d-flex gap-2">
            <form action="{{ route('payments.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Cari pembayaran..." value="{{ request()->get('search') }}" style="width: 200px;">
                <button type="submit" class="btn btn-modern btn-outline-modern">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <a href="{{ route('payments.create') }}" class="btn btn-modern btn-primary-modern">
                <i class="fas fa-plus"></i> Tambah Pembayaran
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Nama Pembayaran</th>
                        <th>Nomor Pembayaran</th>
                        <th class="text-center">Logo</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold">{{ $payment->nama_pembayaran }}</div>
                        </td>
                        <td>
                            <code class="bg-light px-2 py-1 rounded">{{ $payment->nomor_pembayaran }}</code>
                        </td>
                        <td class="text-center">
                            @if ($payment->gambar_pembayaran)
                                <img src="{{ asset('storage/' . $payment->gambar_pembayaran) }}" alt="Logo" style="max-width: 60px; height: 30px; object-fit: contain;">
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('payments.edit', $payment->id) }}" class="btn btn-sm btn-modern btn-warning-modern" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form onsubmit="return confirm('Yakin ingin menghapus pembayaran ini?');" action="{{ route('payments.destroy', $payment->id) }}" method="POST" class="d-inline">
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
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="fas fa-credit-card fa-2x mb-2 d-block"></i>
                            Tidak ada data pembayaran
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
    <div class="card-footer d-flex justify-content-center">
        {{ $payments->links() }}
    </div>
    @endif
</div>
@endsection
