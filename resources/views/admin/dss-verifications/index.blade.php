@extends('layouts.admin-modern')

@section('page-title', 'Verifikasi DSS')
@section('page-subtitle', 'Review pengajuan data DSS dari penjaga jalur')

@section('main-content')
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Pending Submission</p>
                        <h3 class="value mb-0">{{ $pendingCount }}</h3>
                    </div>
                    <div class="icon warning"><i class="fas fa-hourglass-half"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modern-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-clipboard-check"></i> Daftar Pengajuan DSS Pending</h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Jalur</th>
                            <th>Pengaju</th>
                            <th>Panorama</th>
                            <th>Fasilitas</th>
                            <th>Keamanan</th>
                            <th>Keramaian</th>
                            <th>Popularitas</th>
                            <th>Diajukan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submissions as $submission)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $submission->route->nama ?? '-' }}</div>
                                    <small class="text-muted">Route ID: {{ $submission->route_id }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $submission->submitter->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $submission->submitter->email ?? '-' }}</small>
                                </td>
                                <td>{{ (int) $submission->panorama_score_pending }}</td>
                                <td>{{ (int) $submission->fasilitas_score_pending }}</td>
                                <td>{{ (int) $submission->safety_score_pending }}</td>
                                <td>{{ (int) $submission->crowd_level_pending }}</td>
                                <td>{{ (int) ($submission->popularity_score_pending ?? 0) }}</td>
                                <td>
                                    <div>{{ optional($submission->created_at)->timezone('Asia/Jakarta')->format('d M Y') }}</div>
                                    <small class="text-muted">{{ optional($submission->created_at)->timezone('Asia/Jakarta')->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <form action="{{ route('admin.dss-verifications.approve', $submission->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success-modern">Approve</button>
                                        </form>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger-modern"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#rejectForm{{ $submission->id }}"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="collapse" id="rejectForm{{ $submission->id }}">
                                <td colspan="9" class="bg-light-subtle">
                                    <form action="{{ route('admin.dss-verifications.reject', $submission->id) }}" method="POST" class="row g-2 align-items-end">
                                        @csrf
                                        <div class="col-md-10">
                                            <label class="form-label">Alasan penolakan</label>
                                            <textarea name="reason" class="form-control" rows="2" placeholder="Jelaskan alasan penolakan agar penjaga bisa memperbaiki data..." required></textarea>
                                        </div>
                                        <div class="col-md-2 d-grid">
                                            <button type="submit" class="btn btn-danger">Kirim Reject</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Tidak ada pengajuan DSS yang menunggu verifikasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($submissions->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
@endsection
