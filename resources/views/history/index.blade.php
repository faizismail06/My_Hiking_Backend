@extends('layouts.admin-modern')

@section('page-title', 'Riwayat Pesanan')
@section('page-subtitle', 'Lihat dan kelola riwayat pesanan pendakian')

@section('main-content')
<!-- QR Scanner Card -->
<div class="modern-card animate-fade-in mb-4">
    <div class="card-header">
        <h5><i class="fas fa-qrcode"></i> Scan QR Code</h5>
    </div>
    <div class="card-body">
        <div id="reader" style="width: 100%; max-width: 400px; margin: auto;"></div>
    </div>
</div>

<!-- History Table -->
<div class="modern-card animate-fade-in" style="animation-delay: 0.1s">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5><i class="fas fa-history"></i> Data Riwayat</h5>
        <form action="{{ route('history.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
            <input type="text" name="search" class="form-control" placeholder="Cari ID pesanan..." value="{{ request()->get('search') }}" style="width: 180px;">
            <select name="status" class="form-select" style="width: 150px;">
                <option value="" {{ request()->get('status') == '' ? 'selected' : '' }}>Semua Status</option>
                <option value="Booking" {{ request()->get('status') == 'Booking' ? 'selected' : '' }}>Booking</option>
                <option value="Sedang Mendaki" {{ request()->get('status') == 'Sedang Mendaki' ? 'selected' : '' }}>Mendaki</option>
                <option value="Selesai" {{ request()->get('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
            <button type="submit" class="btn btn-modern btn-primary-modern">
                <i class="fas fa-search"></i> Cari
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>ID Pesanan</th>
                        <th>Ketua</th>
                        <th class="text-center">Tanggal Naik</th>
                        <th class="text-center">Tanggal Turun</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $p)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <code class="bg-light px-2 py-1 rounded">#{{ $p->id }}</code>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm" style="width: 32px; height: 32px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <span style="color: white; font-size: 12px; font-weight: 600;">{{ $p->user ? strtoupper(substr($p->user->name, 0, 1)) : '?' }}</span>
                                </div>
                                <div class="fw-semibold">{{ $p->user ? $p->user->name : 'Tidak Diketahui' }}</div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="small">{{ \Carbon\Carbon::parse($p->tanggal_naik)->format('d M Y') }}</span>
                        </td>
                        <td class="text-center">
                            <span class="small">{{ \Carbon\Carbon::parse($p->tanggal_turun)->format('d M Y') }}</span>
                        </td>
                        <td class="text-center">
                            @if ($p->status == 'Booking')
                                <span class="modern-badge badge-warning">Booking</span>
                            @elseif ($p->status == 'Sedang Mendaki')
                                <span class="modern-badge badge-success">Mendaki</span>
                            @elseif ($p->status == 'Selesai')
                                <span class="modern-badge badge-secondary">Selesai</span>
                            @else
                                <span class="modern-badge badge-info">{{ $p->status }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('history.show', $p->id) }}" class="btn btn-sm btn-modern btn-outline-modern" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-history fa-2x mb-2 d-block"></i>
                            Tidak ada data riwayat
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
function onScanSuccess(decodedText, decodedResult) {
    console.log(`Code matched = ${decodedText}`, decodedResult);
    window.location.href = `/history/${decodedText}`;
}

function onScanFailure(error) {
    console.warn(`Code scan error = ${error}`);
}

let html5QrcodeScanner = new Html5QrcodeScanner(
    "reader",
    { fps: 10, qrbox: { width: 250, height: 250 } },
    false
);
html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>
@endsection
