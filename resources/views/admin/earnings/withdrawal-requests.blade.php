@extends('layouts.admin-modern')

@section('page-title', 'Withdrawal Requests')
@section('page-subtitle', 'Kelola dan proses request penarikan saldo dari penjaga jalur')

@section('main-content')
    <!-- Filters -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="modern-card animate-fade-in">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.earnings.withdrawal-requests') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>
                                    Approved</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>
                                    Rejected</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                                    Completed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Penjaga Jalur</label>
                            <select name="user_id" class="form-select">
                                <option value="">Semua Penjaga</option>
                                @foreach ($trailGuards as $guard)
                                    <option value="{{ $guard->id }}"
                                        {{ request('user_id') === $guard->id ? 'selected' : '' }}>
                                        {{ $guard->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control"
                                value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.earnings.withdrawal-requests') }}" class="btn btn-light ms-2">
                                <i class="bx bx-refresh"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if ($errors->any())
        <div class="alert alert-modern alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-modern alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-modern alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Withdrawal Requests Table -->
    <div class="row g-4">
        <div class="col-12">
            <div class="modern-card animate-fade-in">
                <div class="card-header">
                    <h5><i class="fas fa-list-check"></i> Daftar Request Penarikan</h5>
                </div>
                <div class="card-body">
                    <div style="overflow-x: auto;">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Tanggal Request</th>
                                    <th>Penjaga Jalur</th>
                                    <th>Metode Penarikan</th>
                                    <th>Jumlah Request</th>
                                    <th>Biaya Admin</th>
                                    <th>Jumlah Bersih</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawalRequests as $request)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $request->created_at->format('d M Y') }}</strong>
                                                <br />
                                                <small
                                                    class="text-muted">{{ $request->created_at->format('H:i:s') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div
                                                    style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary-light); display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-weight: 600; font-size: 0.85rem;">
                                                    {{ strtoupper(substr($request->user->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $request->user->name }}</strong>
                                                    <br />
                                                    <small style="color: #64748b;">{{ $request->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($request->withdrawal_method === 'bank_transfer')
                                                <span class="badge-modern"
                                                    style="background: #dbeafe; color: var(--info-color);">
                                                    <i class="fas fa-building"></i> Bank Transfer
                                                </span>
                                                <br />
                                                <small
                                                    style="color: #64748b; font-size: 0.8rem;">{{ $request->bank_name ?? '-' }}<br />{{ substr($request->account_number, -4) ?? '-' }}</small>
                                            @else
                                                <span class="badge-modern"
                                                    style="background: #fef3c7; color: var(--warning-color);">
                                                    <i class="fas fa-mobile-alt"></i> E-Wallet
                                                </span>
                                                <br />
                                                <small
                                                    style="color: #64748b; font-size: 0.8rem;">{{ $request->e_wallet_type ?? '-' }}<br />{{ substr($request->e_wallet_number, -4) ?? '-' }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>Rp {{ number_format($request->amount, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-danger">Rp
                                                {{ number_format($request->admin_fee, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            <strong class="text-success">Rp
                                                {{ number_format($request->net_amount, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => ['bg' => '#fef3c7', 'text' => '#d97706'],
                                                    'approved' => ['bg' => '#dbeafe', 'text' => '#2563eb'],
                                                    'rejected' => ['bg' => '#fee2e2', 'text' => '#dc2626'],
                                                    'completed' => ['bg' => '#dcfce7', 'text' => '#16a34a'],
                                                ];
                                                $color = $statusColors[$request->status] ?? [
                                                    'bg' => '#f1f5f9',
                                                    'text' => '#475569',
                                                ];
                                            @endphp
                                            <span class="badge-modern"
                                                style="background: {{ $color['bg'] }}; color: {{ $color['text'] }};">
                                                {{ $request->getStatusLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm" data-bs-toggle="dropdown" aria-expanded="false"
                                                    style="background: #f1f5f9; border: none; color: #475569; border-radius: 10px; padding: 0.5rem 1rem;">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end m-0">
                                                    <a class="dropdown-item"
                                                        href="{{ route('admin.earnings.withdrawal-request-detail', $request->id) }}">
                                                        <i class="fas fa-eye me-2"></i> Lihat Detail
                                                    </a>
                                                    @if ($request->status === 'pending')
                                                        <a class="dropdown-item" href="javascript:void(0);"
                                                            onclick="approveRequest({{ $request->id }})">
                                                            <i class="fas fa-check me-2"></i> Setujui
                                                        </a>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                            onclick="rejectRequest({{ $request->id }})">
                                                            <i class="fas fa-times me-2"></i> Tolak
                                                        </a>
                                                    @endif
                                                    @if ($request->status === 'approved')
                                                        <a class="dropdown-item text-success" href="javascript:void(0);"
                                                            onclick="completeRequest({{ $request->id }})">
                                                            <i class="fas fa-check-circle me-2"></i> Tandai Selesai
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" style="text-align: center; color: #64748b; padding: 2rem;">
                                            <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                            <p style="margin-top: 0.5rem;">Tidak ada request penarikan</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                    <div style="color: #64748b; font-size: 0.85rem;">
                        Menampilkan {{ $withdrawalRequests->count() }} dari {{ $withdrawalRequests->total() }} data
                    </div>
                    {{ $withdrawalRequests->links() }}
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
                    <h5 class="modal-title"><i class="fas fa-check me-2"></i> Setujui Request Penarikan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menyetujui request penarikan ini?</p>
                        <div class="alert alert-modern alert-info">
                            <i class="fas fa-info-circle"></i>
                            <small>Request akan dipindahkan ke status "Approved" dan siap untuk ditandai selesai.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-modern" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-modern btn-primary-modern">Setujui</button>
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
                    <h5 class="modal-title"><i class="fas fa-times me-2"></i> Tolak Request Penarikan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan</label>
                            <textarea name="rejection_reason" class="form-control" rows="4" required
                                placeholder="Jelaskan alasan penolakan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-modern" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-modern btn-danger-modern">Tolak</button>
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
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i> Tandai Request Selesai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="completeForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p>Apakah Anda yakin bahwa transfer saldo telah selesai dilakukan?</p>
                        <div class="alert alert-modern alert-success">
                            <i class="fas fa-check-circle"></i>
                            <small>Saldo penjaga jalur akan dikurangi dan catatan akan disimpan sebagai riwayat
                                penarikan.</small>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Bukti Transfer (Opsional)</label>
                            <input type="file" name="transfer_proof" class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Format: JPG, PNG, PDF. Maksimal 4MB.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-modern" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-modern btn-success-modern">Tandai Selesai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
        const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        const completeModal = new bootstrap.Modal(document.getElementById('completeModal'));

        function approveRequest(requestId) {
            document.getElementById('approveForm').action = `/admin/earnings/withdrawal/${requestId}/approve`;
            approveModal.show();
        }

        function rejectRequest(requestId) {
            document.getElementById('rejectForm').action = `/admin/earnings/withdrawal/${requestId}/reject`;
            rejectModal.show();
        }

        function completeRequest(requestId) {
            document.getElementById('completeForm').action = `/admin/earnings/withdrawal/${requestId}/complete`;
            completeModal.show();
        }
    </script>
@endsection
