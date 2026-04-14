@extends('layouts.admin-modern')

@section('page-title', 'Detail Refund Request')
@section('page-subtitle', 'Lihat hasil proses refund dan bukti transfer')

@section('main-content')
    @php
        $statusClass = match($refundRequest->refund_status) {
            'pending' => 'badge-pending',
            'approved' => 'badge-info',
            'rejected' => 'badge-danger',
            'refunded' => 'badge-success',
            default => 'badge-secondary',
        };
    @endphp

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-file-invoice-dollar"></i> Refund Request #{{ $refundRequest->id }}</h5>
                    <span class="badge-modern {{ $statusClass }}">{{ strtoupper($refundRequest->refund_status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Order ID</small>
                            <div class="fw-semibold">#{{ $refundRequest->order_id }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">User</small>
                            <div class="fw-semibold">{{ $refundRequest->user?->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Gunung / Jalur</small>
                            <div>{{ $refundRequest->order?->mountain?->nama ?? '-' }} / {{ $refundRequest->order?->trail?->nama ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Tanggal Naik</small>
                            <div>{{ $refundRequest->order?->tanggal_naik ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Alasan Pembatalan</small>
                            <div class="fw-semibold">{{ $refundRequest->cancel_reason }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Refund Amount</small>
                            <div class="fw-semibold text-success">Rp {{ number_format($refundRequest->refund_amount, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Penalty Amount</small>
                            <div class="fw-semibold text-danger">Rp {{ number_format($refundRequest->penalty_amount, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Metode Refund</small>
                            <div class="fw-semibold">{{ $refundRequest->refund_method }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nomor Rekening / Nomor HP</small>
                            <div>{{ $refundRequest->account_number ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nama Pemilik</small>
                            <div>{{ $refundRequest->account_holder ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> Distribusi Penalty / Refund</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <div class="small text-muted">Admin Penalty Share (10%)</div>
                                <div class="fw-semibold">Rp {{ number_format($distribution['admin_penalty_share'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <div class="small text-muted">Ranger Penalty Share (90%)</div>
                                <div class="fw-semibold">Rp {{ number_format($distribution['ranger_penalty_share'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <div class="small text-muted">Admin Refund Basis (10%)</div>
                                <div class="fw-semibold">Rp {{ number_format($distribution['admin_refund_share'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <div class="small text-muted">Ranger Refund Basis (90%)</div>
                                <div class="fw-semibold">Rp {{ number_format($distribution['ranger_refund_share'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="modern-card">
                <div class="card-header">
                    <h5><i class="fas fa-timeline"></i> Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-muted">Requested At</div>
                        <div>{{ optional($refundRequest->requested_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-muted">Processed At</div>
                        <div>{{ $refundRequest->processed_at ? optional($refundRequest->processed_at)->timezone('Asia/Jakarta')->format('d M Y H:i') : '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-muted">Order Status</div>
                        <div>{{ $refundRequest->order?->status ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="modern-card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-image"></i> Bukti Transfer</h5>
                </div>
                <div class="card-body text-center">
                    @if ($refundRequest->proof_of_transfer)
                        <a href="{{ asset('storage/' . $refundRequest->proof_of_transfer) }}" target="_blank">
                            <img src="{{ asset('storage/' . $refundRequest->proof_of_transfer) }}" alt="Bukti Refund" class="img-fluid rounded">
                        </a>
                    @else
                        <div class="py-4 text-muted">Belum ada bukti transfer.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.refunds.index') }}" class="btn btn-modern btn-outline-modern">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
@endsection
