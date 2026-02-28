@extends('layouts.admin-modern')

@section('page-title', 'Daftar Transaksi')
@section('page-subtitle', 'Kelola dan verifikasi transaksi pembayaran')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-receipt"></i> Data Transaksi</h5>
        <form action="{{ route('transactions.index') }}" method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Cari transaksi..." value="{{ request()->get('search') }}" style="width: 200px;">
            <button type="submit" class="btn btn-modern btn-outline-modern">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>ID Pesanan</th>
                        <th>Metode Pembayaran</th>
                        <th class="text-center">Total Bayar</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $key => $transaction)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td>
                            <code class="bg-light px-2 py-1 rounded">{{ $transaction->id_pesanan }}</code>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $transaction->payment->nama_pembayaran ?? 'Tidak ditemukan' }}</div>
                        </td>
                        <td class="text-center">
                            <span class="fw-semibold">Rp {{ number_format($transaction->total_bayar, 0, ',', '.') }}</span>
                        </td>
                        <td class="text-center">
                            @if ($transaction->status_pesanan === 'Unverified')
                                <span class="modern-badge badge-danger">Unverified</span>
                            @elseif ($transaction->status_pesanan === 'Verified')
                                <span class="modern-badge badge-success">Verified</span>
                            @elseif ($transaction->status_pesanan === 'Incomplete')
                                <span class="modern-badge badge-warning">Incomplete</span>
                            @else
                                <span class="modern-badge badge-secondary">{{ $transaction->status_pesanan }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-sm btn-modern btn-outline-modern" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if ($transaction->status_pesanan === 'Unverified')
                                    <form action="{{ route('transactions.verify', $transaction->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-modern btn-success-modern" title="Verifikasi">
                                            <i class="fas fa-check"></i> Verifikasi
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                            Tidak ada data transaksi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
