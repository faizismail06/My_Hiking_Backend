@extends('layouts.admin-modern')

@section('main-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Permintaan Penarikan Saldo</h4>
                <p class="text-muted mb-0">Lihat rincian request, status proses, dan batalkan request jika masih pending.</p>
            </div>
            <a href="{{ route('trail-guard.withdrawal.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
                            <div>
                                <div class="text-muted small text-uppercase mb-1">Request ID</div>
                                <h5 class="mb-0">#{{ $withdrawalRequest->id }}</h5>
                            </div>
                            <div class="text-md-end">
                                <div class="text-muted small text-uppercase mb-1">Status</div>
                                <span class="badge bg-label-{{ $withdrawalRequest->getStatusBadgeClass() }} fs-6 px-3 py-2">
                                    {{ $withdrawalRequest->getStatusLabel() }}
                                </span>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small mb-1">Tanggal Pengajuan</div>
                                    <div class="fw-semibold">{{ $withdrawalRequest->created_at->format('d M Y, H:i:s') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small mb-1">
                                        {{ $withdrawalRequest->status === 'rejected' ? 'Tanggal Penolakan' : 'Tanggal Persetujuan' }}
                                    </div>
                                    <div class="fw-semibold">
                                        {{ $withdrawalRequest->approved_at ? $withdrawalRequest->approved_at->format('d M Y, H:i:s') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small mb-1">Jumlah Pengajuan</div>
                                    <div class="fw-bold text-primary fs-5">Rp {{ number_format($withdrawalRequest->amount, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small mb-1">Biaya Admin</div>
                                    <div class="fw-bold text-danger fs-5">Rp {{ number_format($withdrawalRequest->admin_fee, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light border rounded-3 p-3 h-100">
                                    <div class="text-muted small mb-1">Jumlah Bersih</div>
                                    <div class="fw-bold text-success fs-5">Rp {{ number_format($withdrawalRequest->net_amount, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="mb-3">Detail Penerima Dana</h6>
                                @if ($withdrawalRequest->withdrawal_method === 'bank_transfer')
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="text-muted small mb-1">Metode</div>
                                            <div class="fw-semibold">Bank Transfer</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-muted small mb-1">Bank</div>
                                            <div class="fw-semibold">{{ $withdrawalRequest->bank_name }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-muted small mb-1">Nama Pemegang</div>
                                            <div class="fw-semibold">{{ $withdrawalRequest->account_holder }}</div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="text-muted small mb-1">Nomor Rekening</div>
                                            <div class="fw-semibold">{{ $withdrawalRequest->account_number }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="text-muted small mb-1">Metode</div>
                                            <div class="fw-semibold">E-Wallet</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-muted small mb-1">Tipe E-Wallet</div>
                                            <div class="fw-semibold">{{ ucfirst($withdrawalRequest->e_wallet_type) }}</div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="text-muted small mb-1">Nomor E-Wallet</div>
                                            <div class="fw-semibold">{{ $withdrawalRequest->e_wallet_number }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if ($withdrawalRequest->status === 'rejected' && $withdrawalRequest->rejection_reason)
                            <div class="alert alert-danger mt-4 mb-0">
                                <div class="fw-semibold mb-1">Alasan Penolakan</div>
                                <div>{{ $withdrawalRequest->rejection_reason }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Status Request</h5>
                        @if ($withdrawalRequest->status === 'pending')
                            <div class="alert alert-warning mb-0">
                                <div class="fw-semibold mb-1">Menunggu Persetujuan</div>
                                <small>Request Anda sedang menunggu review dari admin. Selama masih pending, request masih bisa dibatalkan.</small>
                            </div>
                        @elseif ($withdrawalRequest->status === 'approved')
                            <div class="alert alert-info mb-0">
                                <div class="fw-semibold mb-1">Sudah Disetujui</div>
                                <small>Request sudah disetujui. Transfer dana akan diproses oleh admin.</small>
                            </div>
                        @elseif ($withdrawalRequest->status === 'rejected')
                            <div class="alert alert-danger mb-0">
                                <div class="fw-semibold mb-1">Request Ditolak</div>
                                <small>Permintaan pencairan ditolak. Cek alasan penolakan pada detail di sebelah kiri.</small>
                            </div>
                        @else
                            <div class="alert alert-success mb-0">
                                <div class="fw-semibold mb-1">Transfer Selesai</div>
                                <small>Dana telah berhasil ditransfer ke rekening atau e-wallet yang Anda pilih.</small>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Ringkasan Saldo</h5>
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Total Pendapatan Kotor</div>
                            <div class="fw-semibold">Rp {{ number_format(auth()->user()->total_earnings ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Total Saldo Sudah Diproses</div>
                            <div class="fw-semibold text-success">Rp {{ number_format(auth()->user()->withdrawn_amount ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Total Fee Admin Terpotong</div>
                            <div class="fw-semibold text-warning">Rp {{ number_format($withdrawalSummary['admin_fee_paid'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="rounded-3 bg-light border p-3">
                            <div class="text-muted small mb-1">Saldo Tersedia untuk Ditarik</div>
                            <div class="fw-bold">Rp {{ number_format(auth()->user()->available_balance ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Timeline</h5>
                        <div class="d-flex gap-3 mb-3">
                            <div class="rounded-circle bg-success flex-shrink-0" style="width: 12px; height: 12px; margin-top: 6px;"></div>
                            <div>
                                <div class="fw-semibold">Pengajuan Dibuat</div>
                                <div class="text-muted small">{{ $withdrawalRequest->created_at->format('d M Y, H:i') }}</div>
                            </div>
                        </div>

                        @if ($withdrawalRequest->approved_at)
                            <div class="d-flex gap-3 mb-3">
                                <div class="rounded-circle {{ $withdrawalRequest->status === 'rejected' ? 'bg-danger' : 'bg-info' }} flex-shrink-0"
                                    style="width: 12px; height: 12px; margin-top: 6px;"></div>
                                <div>
                                    <div class="fw-semibold">
                                        {{ $withdrawalRequest->status === 'rejected' ? 'Ditolak Admin' : 'Disetujui Admin' }}
                                    </div>
                                    <div class="text-muted small">{{ $withdrawalRequest->approved_at->format('d M Y, H:i') }}</div>
                                </div>
                            </div>
                        @endif

                        @if ($withdrawalRequest->completed_at)
                            <div class="d-flex gap-3">
                                <div class="rounded-circle bg-success flex-shrink-0" style="width: 12px; height: 12px; margin-top: 6px;"></div>
                                <div>
                                    <div class="fw-semibold">Transfer Selesai</div>
                                    <div class="text-muted small">{{ $withdrawalRequest->completed_at->format('d M Y, H:i') }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($withdrawalRequest->status === 'pending')
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Aksi</h5>
                            <p class="text-muted small mb-3">Kalau ada data yang salah, Anda bisa membatalkan request ini lalu membuat request baru.</p>
                            <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal"
                                data-bs-target="#cancelModal">
                                <i class="bx bx-x me-1"></i> Batalkan Permintaan
                            </button>
                        </div>
                    </div>
                @endif

                @if ($withdrawalRequest->status === 'completed')
                    <div class="card shadow-sm border-0 mt-4">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Rincian Transfer Selesai</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Jumlah penarikan</span>
                                <strong>Rp {{ number_format($withdrawalRequest->amount, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Fee admin ke sistem</span>
                                <strong class="text-warning">Rp {{ number_format($withdrawalRequest->admin_fee, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Dana diterima Anda</span>
                                <strong class="text-success">Rp {{ number_format($withdrawalRequest->net_amount, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Batalkan Permintaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('trail-guard.withdrawal.cancel', $withdrawalRequest->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p class="mb-3">Apakah Anda yakin ingin membatalkan permintaan penarikan ini?</p>
                        <div class="alert alert-info mb-0">
                            <small>Pembatalan hanya dapat dilakukan jika request masih berstatus <strong>Pending</strong>.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger">Ya, Batalkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
