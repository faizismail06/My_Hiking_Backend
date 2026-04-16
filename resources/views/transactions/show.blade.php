@extends('layouts.admin-modern')

@section('page-title', 'Detail Transaksi')
@section('page-subtitle', 'Lihat rincian transaksi pembayaran')

@section('main-content')
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Rincian Transaksi -->
            <div class="modern-card animate-fade-in">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-receipt"></i> Rincian Transaksi</h5>
                    @if ($transaction->status_pesanan === 'Complete')
                        <span class="modern-badge badge-success">Complete</span>
                    @else
                        <span class="modern-badge badge-warning">Incomplete</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted d-block">ID Transaksi</small>
                                <span class="fw-semibold">#{{ $transaction->id }}</span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">ID Pesanan</small>
                                <code class="bg-light px-2 py-1 rounded">{{ $transaction->id_pesanan }}</code>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Metode Pembayaran</small>
                                <span class="fw-semibold">{{ $transaction->payment_method_name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted d-block">Total Bayar</small>
                                <span class="fw-semibold fs-4 text-primary">Rp
                                    {{ number_format($transaction->total_bayar, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Waktu Pembayaran (Lengkap) -->
            <div class="modern-card animate-fade-in" style="animation-delay: 0.05s">
                <div class="card-header">
                    <h5><i class="fas fa-clock"></i> Detail Waktu Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted d-block"><i class="fas fa-calendar me-1"></i>Tanggal
                                    Pembayaran</small>
                                <span class="fw-semibold">
                                    @if ($transaction->waktu_pembayaran)
                                        {{ \Carbon\Carbon::parse($transaction->waktu_pembayaran)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                    @else
                                        <span class="text-danger">Belum dibayar</span>
                                    @endif
                                </span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block"><i class="fas fa-hourglass-end me-1"></i>Waktu
                                    Pembayaran</small>
                                <span class="fw-semibold fs-5">
                                    @if ($transaction->waktu_pembayaran)
                                        {{ \Carbon\Carbon::parse($transaction->waktu_pembayaran)->format('H:i:s') }}
                                    @else
                                        <span class="text-danger">-</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted d-block"><i class="fas fa-info-circle me-1"></i>Status
                                    Pembayaran</small>
                                <span class="fw-semibold">
                                    @if ($transaction->status_pesanan === 'Complete')
                                        <span class="badge badge-success"><i
                                                class="fas fa-check-circle me-1"></i>Berhasil</span>
                                    @else
                                        <span class="badge badge-warning"><i
                                                class="fas fa-exclamation-circle me-1"></i>{{ $transaction->status_pesanan }}</span>
                                    @endif
                                </span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block"><i class="fas fa-history me-1"></i>Waktu Pembuatan
                                    Transaksi</small>
                                <span class="fw-semibold small">
                                    {{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y') }} <br>
                                    <span class="text-muted">pukul
                                        {{ \Carbon\Carbon::parse($transaction->created_at)->format('H:i:s') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    @if ($transaction->waktu_pembayaran)
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Pembayaran Lengkap:</strong>
                            {{ \Carbon\Carbon::parse($transaction->waktu_pembayaran)->locale('id')->isoFormat('dddd, D MMMM Y [pukul] HH:mm:ss') }}
                        </div>
                    @else
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Pembayaran Belum Selesai</strong> - Menunggu konfirmasi pembayaran dari pelanggan
                        </div>
                    @endif
                </div>
            </div>
            <div class="modern-card animate-fade-in" style="animation-delay: 0.1s">
                <div class="card-header">
                    <h5><i class="fas fa-mountain"></i> Informasi Jalur & Gunung</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted d-block">Gunung</small>
                                <span class="fw-semibold">{{ $transaction->order->jalur->gunung->nama ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted d-block">Jalur</small>
                                <span class="fw-semibold">{{ $transaction->order->jalur->nama ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="modern-card animate-fade-in" style="animation-delay: 0.1s">
                <div class="card-header">
                    <h5><i class="fas fa-image"></i> Bukti Pembayaran</h5>
                </div>
                <div class="card-body text-center">
                    @if ($transaction->bukti)
                        <img src="{{ asset('/storage/' . $transaction->bukti) }}" alt="Bukti Pembayaran"
                            class="img-fluid rounded" style="max-height: 400px; object-fit: cover;">
                    @else
                        <div class="py-5 text-muted">
                            <i class="fas fa-image fa-3x mb-3 d-block"></i>
                            <p class="mb-0">Tidak ada bukti pembayaran</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Anggota Pesanan -->
    <div class="modern-card animate-fade-in" style="animation-delay: 0.2s; margin-top: 2rem;">
        <div class="card-header">
            <h5><i class="fas fa-users"></i> Daftar Anggota Pesanan</h5>
            <span class="badge-modern badge-done">{{ $transaction->order->orderMembers->count() + 1 }} Orang</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Nama</th>
                            <th>No HP</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Pembooking (Ketua) -->
                        <tr>
                            <td class="text-center"><span class="badge bg-primary">K</span></td>
                            <td>
                                <div class="fw-semibold">{{ $transaction->order->user->name }}</div>
                            </td>
                            <td>
                                <div>{{ $transaction->order->user->phone ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-success">Pembooking (Ketua)</span>
                            </td>
                        </tr>
                        <!-- Anggota Pesanan -->
                        @forelse($transaction->order->orderMembers as $index => $anggota)
                            <tr>
                                <td class="text-center">{{ $index + 2 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $anggota->nama }}</div>
                                </td>
                                <td>
                                    <div>{{ $anggota->no_hp ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">Anggota</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">
                                    Hanya pembooking saja
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('transactions.index') }}" class="btn btn-modern btn-outline-modern">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
        {{-- Verify button removed - payment verified automatically via Midtrans --}}
    </div>
@endsection
