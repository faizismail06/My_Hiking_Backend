@extends('layouts.guards')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Riwayat Permintaan Penarikan Saldo</h4>
                <p class="text-muted mb-0">Lihat status pencairan, buka detail request, atau batalkan request yang masih pending.</p>
            </div>
            <a href="{{ route('trail-guard.withdrawal.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Ajukan Penarikan Baru
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

        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="badge badge-center rounded-pill bg-label-primary p-3">
                                <i class="bx bx-wallet fs-4"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Pendapatan Kotor</div>
                                <h4 class="mb-0 text-primary">Rp {{ number_format($user->total_earnings ?? 0, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <small class="text-muted">Akumulasi seluruh pendapatan dari pesanan yang sudah dibayar.</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="badge badge-center rounded-pill bg-label-success p-3">
                                <i class="bx bx-money fs-4"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Saldo Sudah Diproses</div>
                                <h4 class="mb-0 text-success">Rp {{ number_format($user->withdrawn_amount ?? 0, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <small class="text-muted">Saldo yang sudah keluar dari pendapatan Anda, termasuk fee admin.</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="badge badge-center rounded-pill bg-label-warning p-3">
                                <i class="bx bx-receipt fs-4"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Fee Admin Terpotong</div>
                                <h4 class="mb-0 text-warning">Rp {{ number_format($withdrawalSummary['admin_fee_paid'] ?? 0, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <small class="text-muted">Fee admin menjadi pendapatan sistem pada saat withdrawal selesai.</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="badge badge-center rounded-pill bg-label-info p-3">
                                <i class="bx bx-check-circle fs-4"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Saldo Tersedia</div>
                                <h4 class="mb-0 text-info">Rp {{ number_format($user->available_balance ?? 0, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <small class="text-muted">Saldo yang masih bisa diajukan untuk pencairan.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info d-flex align-items-start gap-2 shadow-sm border-0">
            <i class="bx bx-info-circle fs-4 mt-1"></i>
            <div>
                <div class="fw-semibold mb-1">Cara membaca saldo withdrawal</div>
                <div class="small">
                    "Total pendapatan kotor" adalah seluruh pendapatan Anda. Saat request selesai, "jumlah penarikan" penuh
                    akan mengurangi saldo Anda, sedangkan "fee admin" dicatat sebagai pendapatan sistem admin.
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('trail-guard.withdrawal.index') }}" class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-lg-3 col-md-6 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bx bx-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('trail-guard.withdrawal.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-refresh"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @if ($withdrawalRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Request</th>
                                    <th>Nominal</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($withdrawalRequests as $request)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold">#{{ $request->id }}</div>
                                            <div class="text-muted small">{{ $request->created_at->format('d M Y') }}</div>
                                            <div class="text-muted small">{{ $request->created_at->format('H:i:s') }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-primary">Rp {{ number_format($request->amount, 0, ',', '.') }}</div>
                                            <div class="small text-danger">Biaya admin: Rp {{ number_format($request->admin_fee, 0, ',', '.') }}</div>
                                            <div class="small text-success">Bersih: Rp {{ number_format($request->net_amount, 0, ',', '.') }}</div>
                                        </td>
                                        <td>
                                            @if ($request->withdrawal_method === 'bank_transfer')
                                                <span class="badge bg-label-info mb-1">
                                                    <i class="bx bx-building me-1"></i> Bank Transfer
                                                </span>
                                                <div class="small text-muted">{{ $request->bank_name }}</div>
                                            @else
                                                <span class="badge bg-label-warning mb-1">
                                                    <i class="bx bx-mobile me-1"></i> {{ ucfirst($request->e_wallet_type) }}
                                                </span>
                                                <div class="small text-muted">{{ $request->e_wallet_number }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusBadge = match($request->status) {
                                                    'pending' => 'bg-warning text-dark',
                                                    'approved' => 'bg-info text-white',
                                                    'completed' => 'bg-success text-white',
                                                    'rejected' => 'bg-danger text-white',
                                                    default => 'bg-secondary text-white',
                                                };
                                            @endphp
                                            <span class="badge {{ $statusBadge }} fs-6 px-3 py-2">
                                                {{ $request->getStatusLabel() }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2 flex-wrap">
                                                <a href="{{ route('trail-guard.withdrawal.show', $request->id) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="bx bx-show me-1"></i> Detail
                                                </a>
                                                @if ($request->status === 'pending')
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal" data-bs-target="#cancelModal"
                                                        data-request-id="{{ $request->id }}">
                                                        <i class="bx bx-x me-1"></i> Cancel
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 px-4">
                        <div class="mb-3">
                            <i class="bx bx-wallet fs-1 text-muted"></i>
                        </div>
                        <h5 class="mb-2">Belum ada permintaan penarikan</h5>
                        <p class="text-muted mb-3">Saat kamu mengajukan pencairan saldo, riwayatnya akan muncul di sini.</p>
                        <a href="{{ route('trail-guard.withdrawal.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Ajukan Penarikan Baru
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4">
            <div class="text-muted">
                Menampilkan {{ $withdrawalRequests->count() }} dari {{ $withdrawalRequests->total() }} data
            </div>
            {{ $withdrawalRequests->links() }}
        </div>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Batalkan Permintaan Penarikan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="cancelForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p class="mb-3">Apakah Anda yakin ingin membatalkan permintaan penarikan ini?</p>
                        <div class="alert alert-info mb-0">
                            <small>Pembatalan hanya dapat dilakukan jika status request masih <strong>Pending</strong>.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger">Batalkan Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const cancelModalElement = document.getElementById('cancelModal');

        if (cancelModalElement) {
            cancelModalElement.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const requestId = button?.getAttribute('data-request-id');
                const cancelForm = document.getElementById('cancelForm');

                if (requestId && cancelForm) {
                    cancelForm.action = `/trail-guard/withdrawal/${requestId}/cancel`;
                }
            });
        }
    </script>
@endsection
