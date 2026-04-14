@extends('layouts.admin-modern')

@section('page-title', 'Manual Refund')
@section('page-subtitle', 'Kelola pembatalan tiket dan proses refund manual')

@section('main-content')
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Pending</p>
                        <h3 class="value mb-0">{{ $summary['pending'] }}</h3>
                    </div>
                    <div class="icon warning"><i class="fas fa-hourglass-half"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Approved</p>
                        <h3 class="value mb-0">{{ $summary['approved'] }}</h3>
                    </div>
                    <div class="icon info"><i class="fas fa-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Rejected</p>
                        <h3 class="value mb-0">{{ $summary['rejected'] }}</h3>
                    </div>
                    <div class="icon danger"><i class="fas fa-times"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Refunded</p>
                        <h3 class="value mb-0">{{ $summary['refunded'] }}</h3>
                    </div>
                    <div class="icon success"><i class="fas fa-money-bill-wave"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modern-card">
        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5><i class="fas fa-rotate-left"></i> Daftar Refund Request</h5>

            <form action="{{ route('admin.refunds.index') }}" method="GET" class="d-flex gap-2">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari ID request/order/user"
                    value="{{ request('search') }}"
                    style="width: 220px;"
                >

                <select name="status" class="form-select" style="width: 170px;">
                    <option value="">Semua Status</option>
                    @foreach (['pending', 'approved', 'rejected', 'refunded'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-modern btn-outline-modern">
                    <i class="fas fa-search"></i>
                    Filter
                </button>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Request</th>
                            <th>User</th>
                            <th>Order</th>
                            <th>Refund</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Processed</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($refundRequests as $refund)
                            @php
                                $statusClass = match($refund->refund_status) {
                                    'pending' => 'badge-pending',
                                    'approved' => 'badge-info',
                                    'rejected' => 'badge-danger',
                                    'refunded' => 'badge-success',
                                    default => 'badge-secondary',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">#{{ $refund->id }}</div>
                                    <small class="text-muted">Order #{{ $refund->order_id }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $refund->user->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $refund->user->email ?? '-' }}</small>
                                </td>
                                <td>
                                    <div>{{ $refund->order?->mountain?->nama ?? '-' }}</div>
                                    <small class="text-muted">{{ $refund->order?->trail?->nama ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-success">Rp {{ number_format($refund->refund_amount, 0, ',', '.') }}</div>
                                    <small class="text-danger">Penalty: Rp {{ number_format($refund->penalty_amount, 0, ',', '.') }}</small>
                                    <div class="small text-muted mt-1">
                                        Admin: Rp {{ number_format($refund->penalty_amount * 0.10, 0, ',', '.') }} |
                                        Ranger: Rp {{ number_format($refund->penalty_amount * 0.90, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-modern {{ $statusClass }}">{{ strtoupper($refund->refund_status) }}</span>
                                </td>
                                <td>
                                    <div>{{ optional($refund->requested_at)->timezone('Asia/Jakarta')->format('d M Y') }}</div>
                                    <small class="text-muted">{{ optional($refund->requested_at)->timezone('Asia/Jakarta')->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if ($refund->processed_at)
                                        <div>{{ optional($refund->processed_at)->timezone('Asia/Jakarta')->format('d M Y') }}</div>
                                        <small class="text-muted">{{ optional($refund->processed_at)->timezone('Asia/Jakarta')->format('H:i') }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                                        <a href="{{ route('admin.refunds.show', $refund->id) }}" class="btn btn-sm btn-modern btn-outline-modern">
                                            Detail
                                        </a>

                                        @if ($refund->refund_status === 'pending')
                                            <form action="{{ route('admin.refunds.approve', $refund->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-modern btn-success-modern">Approve</button>
                                            </form>
                                        @endif

                                        @if (in_array($refund->refund_status, ['pending', 'approved'], true))
                                            <form action="{{ route('admin.refunds.reject', $refund->id) }}" method="POST" onsubmit="return confirm('Tolak refund request ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-modern btn-danger-modern">Reject</button>
                                            </form>

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-modern btn-primary-modern"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#refundForm{{ $refund->id }}"
                                            >
                                                Mark Refunded
                                            </button>
                                        @endif

                                        @if ($refund->proof_of_transfer)
                                            <a href="{{ asset('storage/' . $refund->proof_of_transfer) }}" target="_blank" class="btn btn-sm btn-modern btn-outline-modern">
                                                Bukti
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="8" class="bg-light-subtle">
                                    <div class="small text-muted"><strong>Alasan:</strong> {{ $refund->cancel_reason }}</div>
                                    <div class="small text-muted mt-1">
                                        <strong>Metode:</strong> {{ $refund->refund_method }} |
                                        <strong>Rekening/No:</strong> {{ $refund->account_number ?? '-' }} |
                                        <strong>Nama:</strong> {{ $refund->account_holder ?? '-' }}
                                    </div>
                                </td>
                            </tr>
                            @if (in_array($refund->refund_status, ['pending', 'approved'], true))
                                <tr class="collapse" id="refundForm{{ $refund->id }}">
                                    <td colspan="8" class="bg-white">
                                        <form action="{{ route('admin.refunds.refunded', $refund->id) }}" method="POST" enctype="multipart/form-data" class="border rounded p-3">
                                            @csrf
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-8">
                                                    <label class="form-label">Bukti Transfer (jpg/png)</label>
                                                    <input type="file" name="proof_of_transfer" class="form-control" accept="image/png,image/jpeg" required>
                                                </div>
                                                <div class="col-md-4 d-grid">
                                                    <button type="submit" class="btn btn-primary">Simpan Bukti & Mark Refunded</button>
                                                </div>
                                            </div>
                                            <div class="small text-muted mt-2">
                                                Refund amount: <strong>Rp {{ number_format($refund->refund_amount, 0, ',', '.') }}</strong>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Tidak ada refund request.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($refundRequests->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $refundRequests->links() }}
            </div>
        @endif
    </div>
@endsection
