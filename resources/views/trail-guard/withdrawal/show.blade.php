@extends('layouts.admin-modern')

@section('main-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h4 class="fw-bold">Detail Permintaan Penarikan Saldo</h4>
                <p class="text-muted">Rincian lengkap permintaan penarikan saldo Anda</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('trail-guard.withdrawal.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Request Details -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Informasi Permintaan</h5>
                    </div>
                    <div class="card-body">
                        <!-- Request ID and Status -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Request ID</h6>
                                <p class="fw-semibold">#{{ $withdrawalRequest->id }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Status</h6>
                                <span class="badge bg-label-{{ $withdrawalRequest->getStatusBadgeClass() }} fs-6">
                                    {{ $withdrawalRequest->getStatusLabel() }}
                                </span>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Tanggal Pengajuan</h6>
                                <p class="fw-semibold">{{ $withdrawalRequest->created_at->format('d M Y, H:i:s') }}</p>
                            </div>
                            @if ($withdrawalRequest->approved_at)
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Tanggal
                                        {{ $withdrawalRequest->status === 'rejected' ? 'Penolakan' : 'Persetujuan' }}</h6>
                                    <p class="fw-semibold">{{ $withdrawalRequest->approved_at->format('d M Y, H:i:s') }}</p>
                                </div>
                            @endif
                        </div>

                        <hr />

                        <!-- Amount Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Jumlah Pengajuan</h6>
                                <p class="fw-semibold fs-5 text-primary">
                                    Rp {{ number_format($withdrawalRequest->amount, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Biaya Admin</h6>
                                <p class="fw-semibold fs-5 text-danger">
                                    - Rp {{ number_format($withdrawalRequest->admin_fee, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="bg-light p-3 rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Jumlah Bersih</h6>
                                        <p class="fw-bold fs-5 text-success mb-0">
                                            Rp {{ number_format($withdrawalRequest->net_amount, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr />

                        <!-- Withdrawal Details -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted mb-3">Detail Penerima Dana</h6>
                                <div class="bg-light p-3 rounded">
                                    @if ($withdrawalRequest->withdrawal_method === 'bank_transfer')
                                        <h6 class="mb-3"><i class="bx bx-building text-info me-2"></i> Bank Transfer</h6>
                                        <dl class="row">
                                            <dt class="col-sm-4">Bank</dt>
                                            <dd class="col-sm-8">{{ $withdrawalRequest->bank_name }}</dd>
                                            <dt class="col-sm-4">Nama Pemegang</dt>
                                            <dd class="col-sm-8">{{ $withdrawalRequest->account_holder }}</dd>
                                            <dt class="col-sm-4">Nomor Rekening</dt>
                                            <dd class="col-sm-8">
                                                <code>{{ $withdrawalRequest->account_number }}</code>
                                            </dd>
                                        </dl>
                                    @else
                                        <h6 class="mb-3"><i class="bx bx-mobile text-warning me-2"></i> E-Wallet</h6>
                                        <dl class="row">
                                            <dt class="col-sm-4">Tipe E-Wallet</dt>
                                            <dd class="col-sm-8">{{ ucfirst($withdrawalRequest->e_wallet_type) }}</dd>
                                            <dt class="col-sm-4">Nomor</dt>
                                            <dd class="col-sm-8">
                                                <code>{{ $withdrawalRequest->e_wallet_number }}</code>
                                            </dd>
                                        </dl>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Rejection Reason (if rejected) -->
                        @if ($withdrawalRequest->status === 'rejected' && $withdrawalRequest->rejection_reason)
                            <hr />
                            <div class="alert alert-danger mt-4">
                                <h6 class="alert-heading mb-2"><i class="bx bx-info-circle me-2"></i> Alasan Penolakan</h6>
                                <p class="mb-0">{{ $withdrawalRequest->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline & Actions -->
            <div class="col-md-4 mb-4">
                <!-- Timeline -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Timeline Permintaan</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <!-- Requested -->
                            <div class="timeline-event">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content ms-3">
                                    <h6 class="mb-1">Pengajuan Diterima</h6>
                                    <small
                                        class="text-muted d-block">{{ $withdrawalRequest->created_at->format('d M Y, H:i') }}</small>
                                </div>
                            </div>

                            <!-- Approved/Rejected -->
                            @if ($withdrawalRequest->approved_at)
                                <div class="timeline-event">
                                    <div
                                        class="timeline-marker {{ $withdrawalRequest->status === 'rejected' ? 'bg-danger' : 'bg-info' }}">
                                    </div>
                                    <div class="timeline-content ms-3">
                                        <h6 class="mb-1">
                                            {{ $withdrawalRequest->status === 'rejected' ? 'Ditolak' : 'Disetujui Admin' }}
                                        </h6>
                                        <small
                                            class="text-muted d-block">{{ $withdrawalRequest->approved_at->format('d M Y, H:i') }}</small>
                                    </div>
                                </div>
                            @endif

                            <!-- Completed -->
                            @if ($withdrawalRequest->completed_at)
                                <div class="timeline-event">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content ms-3">
                                        <h6 class="mb-1">Proses Selesai</h6>
                                        <small
                                            class="text-muted d-block">{{ $withdrawalRequest->completed_at->format('d M Y, H:i') }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Status Information -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Informasi Status</h5>
                    </div>
                    <div class="card-body">
                        @if ($withdrawalRequest->status === 'pending')
                            <div class="alert alert-warning mb-0">
                                <h6 class="alert-heading mb-2"><i class="bx bx-time text-warning me-2"></i> Menunggu
                                    Persetujuan</h6>
                                <small>Permintaan Anda sedang menunggu persetujuan dari admin. Harap tunggu 1-3 hari
                                    kerja.</small>
                            </div>
                        @elseif($withdrawalRequest->status === 'approved')
                            <div class="alert alert-info mb-0">
                                <h6 class="alert-heading mb-2"><i class="bx bx-check-circle text-info me-2"></i> Telah
                                    Disetujui</h6>
                                <small>Permintaan Anda telah disetujui. Proses transfer akan segera dilakukan.</small>
                            </div>
                        @elseif($withdrawalRequest->status === 'rejected')
                            <div class="alert alert-danger mb-0">
                                <h6 class="alert-heading mb-2"><i class="bx bx-x-circle text-danger me-2"></i> Ditolak</h6>
                                <small>Permintaan Anda telah ditolak. Silakan hubungi admin untuk informasi lebih
                                    lanjut.</small>
                            </div>
                        @else
                            <div class="alert alert-success mb-0">
                                <h6 class="alert-heading mb-2"><i class="bx bx-check-double text-success me-2"></i>
                                    Selesai</h6>
                                <small>Dana telah berhasil ditransfer ke rekening/e-wallet Anda.</small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action Button -->
                @if ($withdrawalRequest->status === 'pending')
                    <div class="card">
                        <div class="card-body">
                            <button class="btn btn-danger w-100" onclick="confirmCancel()">
                                <i class="bx bx-x me-2"></i> Batalkan Permintaan
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
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
                        <p>Apakah Anda yakin ingin membatalkan permintaan penarikan ini?</p>
                        <div class="alert alert-info">
                            <small>Pembatalan hanya dapat dilakukan untuk permintaan dengan status "Pending".</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Batalkan Permintaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));

        function confirmCancel() {
            cancelModal.show();
        }
    </script>
@endsection
