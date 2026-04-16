@extends('layouts.admin-modern')

@section('page-title', 'Daftar Transaksi')
@section('page-subtitle', 'Kelola dan verifikasi transaksi pembayaran')

@section('main-content')
    <div class="modern-card animate-fade-in">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-receipt"></i> Data Transaksi</h5>
            <form action="{{ route('transactions.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Cari transaksi..."
                    value="{{ request()->get('search') }}" style="width: 200px;">
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
                            <th>Jalur</th>
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
                                    <div class="fw-semibold">{{ $transaction->payment_method_name }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $transaction->order->jalur->nama ?? '-' }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-semibold">Rp
                                        {{ number_format($transaction->total_bayar, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($transaction->status_pesanan === 'Incomplete')
                                        <span class="modern-badge badge-warning">Incomplete</span>
                                    @elseif ($transaction->status_pesanan === 'Complete')
                                        <span class="modern-badge badge-success">Complete</span>
                                    @else
                                        <span class="modern-badge badge-secondary">{{ $transaction->status_pesanan }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('transactions.show', $transaction->id) }}"
                                            class="btn btn-sm btn-modern btn-outline-modern" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {{-- Verify button removed - payment verified automatically via Midtrans --}}
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
