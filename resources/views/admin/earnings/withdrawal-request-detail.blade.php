@extends('layouts.admin-modern')

@section('main-content')
    @php
        $statusClasses = [
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'completed' => 'success',
        ];
        $badgeClass = $statusClasses[$withdrawalRequest->status] ?? 'secondary';
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Request Penarikan Saldo</h4>
                <p class="text-muted mb-0">Rincian lengkap permintaan penarikan dari penjaga jalur.</p>
            </div>
            <a href="{{ route('admin.earnings.withdrawal-requests') }}" class="btn btn-outline-secondary">
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
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-2"></i>{{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
                            <div>
                                <p class="text-muted text-uppercase small mb-1">Request ID</p>
                                <h5 class="mb-0">#{{ $withdrawalRequest->id }}</h5>
                            </div>
                            <div class="text-md-end">
                                <p class="text-muted text-uppercase small mb-1">Status Saat Ini</p>
                                <span class="badge bg-label-{{ $badgeClass }} fs-6 px-3 py-2">
                                    @if ($withdrawalRequest->status === 'pending')
                                        Pending
                                    @elseif ($withdrawalRequest->status === 'approved')
                                        Disetujui
                                    @elseif ($withdrawalRequest->status === 'rejected')
                                        Ditolak
                                    @else
                                        Selesai
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <p class="text-muted small mb-1">Dibuat Pada</p>
                                    <div class="fw-semibold">{{ $withdrawalRequest->created_at->format('d M Y, H:i:s') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <p class="text-muted small mb-1">Diproses Pada</p>
                                    <div class="fw-semibold">
                                        {{ $withdrawalRequest->approved_at ? $withdrawalRequest->approved_at->format('d M Y, H:i:s') : '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <p class="text-muted small mb-1">Transfer Selesai</p>
                                    <div class="fw-semibold">
                                        {{ $withdrawalRequest->completed_at ? $withdrawalRequest->completed_at->format('d M Y, H:i:s') : '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <p class="text-muted small mb-1">Admin Pemroses</p>
                                    <div class="fw-semibold">
                                        {{ $withdrawalRequest->approvedByAdmin?->name ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-3 p-3 p-lg-4 mb-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="avatar avatar-lg">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        {{ strtoupper(substr($withdrawalRequest->user->name, 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h5 class="mb-1">{{ $withdrawalRequest->user->name }}</h5>
                                    <div class="text-muted">{{ $withdrawalRequest->user->email }}</div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">No. Telepon</p>
                                    <div class="fw-semibold">{{ $withdrawalRequest->user->phone ?? '-' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Metode Penarikan</p>
                                    <div class="fw-semibold">{{ $withdrawalRequest->getWithdrawalMethodLabel() }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <p class="text-muted small mb-1">Jumlah Request</p>
                                    <div class="fw-bold text-primary fs-5">
                                        Rp {{ number_format($withdrawalRequest->amount, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <p class="text-muted small mb-1">Biaya Admin</p>
                                    <div class="fw-bold text-danger fs-5">
                                        Rp {{ number_format($withdrawalRequest->admin_fee, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 p-3 h-100 border">
                                    <p class="text-muted small mb-1">Jumlah Bersih</p>
                                    <div class="fw-bold text-success fs-5">
                                        Rp {{ number_format($withdrawalRequest->net_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="mb-3">Detail Pencairan</h6>
                                @if ($withdrawalRequest->withdrawal_method === 'bank_transfer')
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="text-muted small mb-1">Bank</div>
                                            <div class="fw-semibold">{{ $withdrawalRequest->bank_name }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-muted small mb-1">Nama Pemegang</div>
                                            <div class="fw-semibold">{{ $withdrawalRequest->account_holder }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-muted small mb-1">Nomor Rekening</div>
                                            <div class="fw-semibold">{{ $withdrawalRequest->account_number }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="text-muted small mb-1">Tipe E-Wallet</div>
                                            <div class="fw-semibold">{{ ucfirst($withdrawalRequest->e_wallet_type) }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-muted small mb-1">Nomor E-Wallet</div>
                                            <div class="fw-semibold">{{ $withdrawalRequest->e_wallet_number }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Aksi Admin</h5>
                        @if ($withdrawalRequest->status === 'pending')
                            <p class="text-muted small mb-3">Request ini masih menunggu keputusan admin.</p>
                            <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal"
                                data-bs-target="#approveModal">
                                <i class="bx bx-check me-1"></i> Setujui Request
                            </button>
                            <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal"
                                data-bs-target="#rejectModal">
                                <i class="bx bx-x me-1"></i> Tolak Request
                            </button>
                        @elseif ($withdrawalRequest->status === 'approved')
                            <p class="text-muted small mb-3">Request sudah disetujui. Tandai selesai setelah transfer dilakukan.</p>
                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal"
                                data-bs-target="#completeModal">
                                <i class="bx bx-check-circle me-1"></i> Tandai Selesai
                            </button>
                        @elseif ($withdrawalRequest->status === 'rejected')
                            <div class="alert alert-danger mb-0">
                                <div class="fw-semibold mb-1">Request sudah ditolak</div>
                                <div class="small">{{ $withdrawalRequest->rejection_reason ?? 'Tidak ada alasan yang dicatat.' }}</div>
                            </div>
                        @else
                            <div class="alert alert-success mb-0">
                                <div class="fw-semibold mb-1">Request sudah selesai</div>
                                <div class="small">Transfer telah ditandai selesai oleh admin.</div>
                                @if ($withdrawalRequest->transfer_proof_path)
                                    <a href="{{ asset('storage/' . $withdrawalRequest->transfer_proof_path) }}" target="_blank"
                                        rel="noopener noreferrer" class="btn btn-sm btn-outline-success mt-3">
                                        <i class="bx bx-link-external me-1"></i> Lihat Bukti Transfer
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Timeline</h5>
                        <div class="d-flex gap-3 mb-3">
                            <div class="rounded-circle bg-success flex-shrink-0" style="width: 12px; height: 12px; margin-top: 6px;"></div>
                            <div>
                                <div class="fw-semibold">Diminta</div>
                                <div class="text-muted small">{{ $withdrawalRequest->created_at->format('d M Y, H:i:s') }}</div>
                            </div>
                        </div>

                        @if ($withdrawalRequest->approved_at)
                            <div class="d-flex gap-3 mb-3">
                                <div
                                    class="rounded-circle {{ $withdrawalRequest->status === 'rejected' ? 'bg-danger' : 'bg-info' }} flex-shrink-0"
                                    style="width: 12px; height: 12px; margin-top: 6px;"></div>
                                <div>
                                    <div class="fw-semibold">
                                        {{ $withdrawalRequest->status === 'rejected' ? 'Ditolak' : 'Disetujui' }}
                                    </div>
                                    <div class="text-muted small">{{ $withdrawalRequest->approved_at->format('d M Y, H:i:s') }}</div>
                                    @if ($withdrawalRequest->approvedByAdmin)
                                        <div class="text-muted small">Oleh {{ $withdrawalRequest->approvedByAdmin->name }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($withdrawalRequest->completed_at)
                            <div class="d-flex gap-3">
                                <div class="rounded-circle bg-success flex-shrink-0" style="width: 12px; height: 12px; margin-top: 6px;"></div>
                                <div>
                                    <div class="fw-semibold">Selesai</div>
                                    <div class="text-muted small">{{ $withdrawalRequest->completed_at->format('d M Y, H:i:s') }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Ringkasan Saldo Penjaga</h5>
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Total Pendapatan</div>
                            <div class="fw-semibold">Rp {{ number_format($withdrawalRequest->user->total_earnings ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Sudah Dicairkan</div>
                            <div class="fw-semibold text-success">Rp {{ number_format($withdrawalRequest->user->withdrawn_amount ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="rounded-3 bg-light border p-3">
                            <div class="text-muted small mb-1">Saldo Tersedia</div>
                            <div class="fw-bold">Rp {{ number_format($withdrawalRequest->user->available_balance ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Request Penarikan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.earnings.withdrawal-request-approve', $withdrawalRequest->id) }}">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3">Apakah Anda yakin ingin menyetujui request penarikan ini?</p>
                        <div class="rounded-3 bg-light border p-3">
                            <div class="small text-muted mb-1">Penjaga Jalur</div>
                            <div class="fw-semibold mb-3">{{ $withdrawalRequest->user->name }}</div>
                            <div class="small text-muted mb-1">Jumlah Bersih</div>
                            <div class="fw-semibold text-success mb-3">Rp {{ number_format($withdrawalRequest->net_amount, 0, ',', '.') }}</div>
                            <div class="small text-muted mb-1">Metode</div>
                            <div class="fw-semibold">{{ $withdrawalRequest->getWithdrawalMethodLabel() }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Setujui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Request Penarikan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.earnings.withdrawal-request-reject', $withdrawalRequest->id) }}">
                    @csrf
                    <div class="modal-body">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required
                            placeholder="Jelaskan alasan penolakan...">{{ old('rejection_reason') }}</textarea>
                        <small class="text-muted">Penjelasan ini akan diterima oleh penjaga jalur.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="completeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tandai Request Selesai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.earnings.withdrawal-request-complete', $withdrawalRequest->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3">Konfirmasi bahwa transfer saldo sudah selesai dilakukan.</p>
                        <div class="rounded-3 bg-success bg-opacity-10 border border-success p-3 mb-3">
                            <div class="fw-semibold mb-2">Yang akan terjadi</div>
                            <ul class="mb-0 ps-3 small">
                                <li>Status request diubah menjadi selesai.</li>
                                <li>Saldo tersedia penjaga akan dikurangi sesuai jumlah bersih.</li>
                                <li>Riwayat transfer akan disimpan.</li>
                            </ul>
                        </div>
                        <label class="form-label">Bukti Transfer (Opsional)</label>
                        <input type="file" name="transfer_proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Format JPG, PNG, PDF. Maksimal 4MB.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Tandai Selesai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
