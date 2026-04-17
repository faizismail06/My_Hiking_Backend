@extends('layouts.admin-modern')

@section('main-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h4 class="fw-bold">Riwayat Permintaan Penarikan Saldo</h4>
                <p class="text-muted">Lihat dan kelola semua permintaan penarikan saldo Anda</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('trail-guard.withdrawal.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Ajukan Penarikan Baru
                </a>
            </div>
        </div>

        <!-- User Balance Summary -->
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="badge badge-center rounded-pill bg-label-primary p-3 me-3">
                                <i class="bx bx-wallet fs-4"></i>
                            </div>
                            <h6 class="mb-0">Total Pendapatan</h6>
                        </div>
                        <h4 class="mb-0 text-primary">
                            Rp {{ number_format($user->total_earnings ?? 0, 0, ',', '.') }}
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="badge badge-center rounded-pill bg-label-success p-3 me-3">
                                <i class="bx bx-money fs-4"></i>
                            </div>
                            <h6 class="mb-0">Sudah Dicairkan</h6>
                        </div>
                        <h4 class="mb-0 text-success">
                            Rp {{ number_format($user->withdrawn_amount ?? 0, 0, ',', '.') }}
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="badge badge-center rounded-pill bg-label-info p-3 me-3">
                                <i class="bx bx-check-circle fs-4"></i>
                            </div>
                            <h6 class="mb-0">Saldo Tersedia</h6>
                        </div>
                        <h4 class="mb-0 text-info">
                            Rp {{ number_format($user->available_balance ?? 0, 0, ',', '.') }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('trail-guard.withdrawal.index') }}" class="row g-3">
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control"
                                    value="{{ request('end_date') }}">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search"></i> Filter
                                </button>
                                <a href="{{ route('trail-guard.withdrawal.index') }}" class="btn btn-light">
                                    <i class="bx bx-refresh"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bx bx-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Withdrawal Requests Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal Request</th>
                                    <th>Jumlah Request</th>
                                    <th>Biaya Admin</th>
                                    <th>Jumlah Bersih</th>
                                    <th>Metode Penarikan</th>
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
                                            <strong>Rp {{ number_format($request->amount, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-danger">- Rp
                                                {{ number_format($request->admin_fee, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            <strong class="text-success">Rp
                                                {{ number_format($request->net_amount, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @if ($request->withdrawal_method === 'bank_transfer')
                                                <span class="badge bg-label-info">
                                                    <i class="bx bx-building"></i> Bank Transfer
                                                </span>
                                                <br />
                                                <small>{{ $request->bank_name }}</small>
                                            @else
                                                <span class="badge bg-label-warning">
                                                    <i class="bx bx-mobile"></i> {{ ucfirst($request->e_wallet_type) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-label-{{ $request->getStatusBadgeClass() }}">
                                                {{ $request->getStatusLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill hide-arrow"
                                                    data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end m-0">
                                                    <a class="dropdown-item"
                                                        href="{{ route('trail-guard.withdrawal.show', $request->id) }}">
                                                        <i class="bx bx-show me-2"></i> Lihat Detail
                                                    </a>
                                                    @if ($request->status === 'pending')
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                            onclick="cancelRequest({{ $request->id }})">
                                                            <i class="bx bx-x me-2"></i> Batalkan
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bx bx-inbox"></i> Belum ada permintaan penarikan
                                            <br />
                                            <small>
                                                <a href="{{ route('trail-guard.withdrawal.create') }}"
                                                    class="text-decoration-none">Buat permintaan penarikan baru</a>
                                            </small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        Menampilkan {{ $withdrawalRequests->count() }} dari {{ $withdrawalRequests->total() }} data
                    </div>
                    {{ $withdrawalRequests->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
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

        function cancelRequest(requestId) {
            document.getElementById('cancelForm').action = `/trail-guard/withdrawal/${requestId}/cancel`;
            cancelModal.show();
        }
    </script>
@endsection
