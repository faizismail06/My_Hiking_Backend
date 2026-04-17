@extends('layouts.admin-modern')

@section('main-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h4 class="fw-bold">Detail Request Penarikan Saldo</h4>
                <p class="text-muted">Rincian lengkap request penarikan dari penjaga jalur</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.earnings.withdrawal-requests') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Request Details Card -->
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
                                <h6 class="text-muted mb-2">Status Saat Ini</h6>
                                @php
                                    $statusClasses = [
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'rejected' => 'danger',
                                        'completed' => 'success',
                                    ];
                                    $badgeClass = $statusClasses[$withdrawalRequest->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-label-{{ $badgeClass }} fs-6 d-inline-block">
                                    @if ($withdrawalRequest->status === 'pending')
                                        🔄 Pending
                                    @elseif($withdrawalRequest->status === 'approved')
                                        ✓ Disetujui
                                    @elseif($withdrawalRequest->status === 'rejected')
                                        ✗ Ditolak
                                    @else
                                        ✓ Selesai
                                    @endif
                                </span>
                            </div>
                        </div>

                        @if ($withdrawalRequest->approved_at)
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Waktu Disetujui / Ditolak</h6>
                                    <p class="fw-semibold">{{ $withdrawalRequest->approved_at->format('d M Y, H:i:s') }}</p>
                                </div>
                                @if ($withdrawalRequest->completed_at)
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Waktu Transfer Selesai</h6>
                                        <p class="fw-semibold">
                                            {{ $withdrawalRequest->completed_at->format('d M Y, H:i:s') }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Penjaga Jalur Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Penjaga Jalur</h6>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg me-3">
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            {{ strtoupper(substr($withdrawalRequest->user->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="fw-semibold mb-0">{{ $withdrawalRequest->user->name }}</p>
                                        <small class="text-muted">{{ $withdrawalRequest->user->email }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">No. Telepon</h6>
                                <p class="fw-semibold">{{ $withdrawalRequest->user->phone ?? '-' }}</p>
                            </div>
                        </div>

                        <hr />

                        <!-- Amount Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Jumlah Request</h6>
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

                        <!-- Withdrawal Method -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted mb-3">Metode Penarikan</h6>
                                <div class="bg-light p-3 rounded">
                                    @if ($withdrawalRequest->withdrawal_method === 'bank_transfer')
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="mb-2"><i class="bx bx-building text-info me-2"></i> Bank
                                                    Transfer</h6>
                                                <dl class="row">
                                                    <dt class="col-sm-5">Bank</dt>
                                                    <dd class="col-sm-7">{{ $withdrawalRequest->bank_name }}</dd>
                                                    <dt class="col-sm-5">Nama Pemegang</dt>
                                                    <dd class="col-sm-7">{{ $withdrawalRequest->account_holder }}</dd>
                                                    <dt class="col-sm-5">Nomor Rekening</dt>
                                                    <dd class="col-sm-7">{{ $withdrawalRequest->account_number }}</dd>
                                                </dl>
                                            </div>
                                        </div>
                                    @else
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="mb-2"><i class="bx bx-mobile text-warning me-2"></i> E-Wallet
                                                </h6>
                                                <dl class="row">
                                                    <dt class="col-sm-5">Tipe E-Wallet</dt>
                                                    <dd class="col-sm-7">{{ ucfirst($withdrawalRequest->e_wallet_type) }}
                                                    </dd>
                                                    <dt class="col-sm-5">Nomor</dt>
                                                    <dd class="col-sm-7">{{ $withdrawalRequest->e_wallet_number }}</dd>
                                                </dl>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline & Action Card -->
            <div class="col-md-4 mb-4">
                <!-- Timeline -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <!-- Requested -->
                            <div class="timeline-event">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content ms-3">
                                    <h6 class="mb-1">Diminta</h6>
                                    <small
                                        class="text-muted d-block">{{ $withdrawalRequest->created_at->format('d M Y, H:i:s') }}</small>
                                </div>
                            </div>

                            <!-- Approved -->
                            @if ($withdrawalRequest->approved_at)
                                <div class="timeline-event">
                                    <div
                                        class="timeline-marker {{ $withdrawalRequest->status === 'approved' ? 'bg-info' : 'bg-success' }}">
                                    </div>
                                    <div class="timeline-content ms-3">
                                        <h6 class="mb-1">
                                            {{ $withdrawalRequest->status === 'rejected' ? 'Ditolak' : 'Disetujui' }}</h6>
                                        <small
                                            class="text-muted d-block">{{ $withdrawalRequest->approved_at->format('d M Y, H:i:s') }}</small>
                                        @if ($withdrawalRequest->approvedByAdmin)
                                            <small class="text-muted d-block">Oleh:
                                                {{ $withdrawalRequest->approvedByAdmin->name }}</small>
                                        @endif
                                        @if ($withdrawalRequest->rejection_reason)
                                            <div class="alert alert-danger alert-sm mt-2 mb-0">
                                                <small><strong>Alasan:</strong>
                                                    {{ $withdrawalRequest->rejection_reason }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Completed -->
                            @if ($withdrawalRequest->completed_at)
                                <div class="timeline-event">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content ms-3">
                                        <h6 class="mb-1">Diselesaikan</h6>
                                        <small
                                            class="text-muted d-block">{{ $withdrawalRequest->completed_at->format('d M Y, H:i:s') }}</small>
                                        @if ($withdrawalRequest->transfer_proof_path)
                                            <a href="{{ asset('storage/' . $withdrawalRequest->transfer_proof_path) }}"
                                                target="_blank" class="btn btn-sm btn-outline-success mt-2">
                                                <i class="fas fa-file-download me-1"></i> Lihat Bukti Transfer
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                @if ($withdrawalRequest->status === 'pending')
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Aksi</h5>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-success w-100 mb-2" onclick="approveRequest()">
                                <i class="bx bx-check me-1"></i> Setujui Request
                            </button>
                            <button class="btn btn-danger w-100" onclick="rejectRequest()">
                                <i class="bx bx-x me-1"></i> Tolak Request
                            </button>
                        </div>
                    </div>
                @elseif($withdrawalRequest->status === 'approved')
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Aksi</h5>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-success w-100" onclick="completeRequest()">
                                <i class="bx bx-check-circle me-1"></i> Tandai Selesai
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Summary Card -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Ringkasan Saldo Penjaga</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Total Pendapatan</h6>
                            <p class="fw-semibold fs-6">
                                Rp {{ number_format($withdrawalRequest->user->total_earnings ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Sudah Dicairkan</h6>
                            <p class="fw-semibold fs-6 text-success">
                                Rp {{ number_format($withdrawalRequest->user->withdrawn_amount ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="bg-light p-2 rounded">
                            <h6 class="text-muted mb-1">Saldo Tersedia</h6>
                            <p class="fw-semibold fs-6">
                                Rp {{ number_format($withdrawalRequest->user->available_balance ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Request Penarikan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST"
                    action="{{ route('admin.earnings.withdrawal-request-approve', $withdrawalRequest->id) }}">
                    @csrf
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menyetujui request penarikan ini?</p>
                        <div class="bg-light p-3 rounded">
                            <h6 class="mb-2">Ringkasan:</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <strong>Penjaga Jalur:</strong> {{ $withdrawalRequest->user->name }}
                                </li>
                                <li class="mb-2">
                                    <strong>Jumlah Bersih:</strong> <span class="text-success">Rp
                                        {{ number_format($withdrawalRequest->net_amount, 0, ',', '.') }}</span>
                                </li>
                                <li>
                                    <strong>Metode:</strong> {{ $withdrawalRequest->getWithdrawalMethodLabel() }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Setujui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Request Penarikan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST"
                    action="{{ route('admin.earnings.withdrawal-request-reject', $withdrawalRequest->id) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="4" required
                                placeholder="Jelaskan alasan penolakan..."></textarea>
                            <small class="text-muted">Penjelasan ini akan diterima oleh penjaga jalur</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete Modal -->
    <div class="modal fade" id="completeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tandai Request Selesai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST"
                    action="{{ route('admin.earnings.withdrawal-request-complete', $withdrawalRequest->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p>Apakah Anda yakin bahwa transfer saldo telah selesai dilakukan?</p>
                        <div class="bg-success bg-opacity-10 border border-success p-3 rounded">
                            <h6 class="mb-2"><i class="bx bx-info-circle text-success me-2"></i>Informasi</h6>
                            <ul class="list-unstyled mb-0 small">
                                <li>✓ Saldo penjaga jalur akan dikurangi</li>
                                <li>✓ Catatan akan disimpan sebagai riwayat penarikan</li>
                                <li>✓ Penjaga jalur akan menerima notifikasi</li>
                            </ul>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Bukti Transfer (Opsional)</label>
                            <input type="file" name="transfer_proof" class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Format: JPG, PNG, PDF. Maksimal 4MB.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Tandai Selesai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
        const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        const completeModal = new bootstrap.Modal(document.getElementById('completeModal'));

        function approveRequest() {
            approveModal.show();
        }

        function rejectRequest() {
            rejectModal.show();
        }

        function completeRequest() {
            completeModal.show();
        }
    </script>
@endsection
