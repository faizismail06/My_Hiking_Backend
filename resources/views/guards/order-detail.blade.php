@extends('layouts.guards')

@section('page-title', 'Detail Pesanan')
@section('page-subtitle', 'Pesanan #' . $order->id . ' - ' . $order->user->name)

@section('content')
    <!-- Status Card -->
    <div class="modern-card mb-4 animate-fade-in">
        <div class="card-body py-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-3 fw-bold">Status Pesanan</h5>
                    <div class="d-flex align-items-center gap-3">
                        @if ($order->status == 'Booking' || $order->status == 'Menunggu Konfirmasi')
                            <span class="badge-modern badge-booking" style="font-size: 1rem; padding: 0.75rem 1.5rem;">
                                <i class="fas fa-clock me-2"></i>{{ $order->status }}
                            </span>
                        @elseif($order->status == 'Dikonfirmasi')
                            <span class="badge-modern badge-verified" style="font-size: 1rem; padding: 0.75rem 1.5rem;">
                                <i class="fas fa-check me-2"></i>{{ $order->status }}
                            </span>
                        @elseif($order->status == 'Sedang Mendaki')
                            <span class="badge-modern badge-hiking" style="font-size: 1rem; padding: 0.75rem 1.5rem;">
                                <i class="fas fa-hiking me-2"></i>{{ $order->status }}
                            </span>
                        @elseif($order->status == 'Selesai')
                            <span class="badge-modern badge-done" style="font-size: 1rem; padding: 0.75rem 1.5rem;">
                                <i class="fas fa-check-circle me-2"></i>{{ $order->status }}
                            </span>
                        @else
                            <span class="badge-modern badge-cancelled" style="font-size: 1rem; padding: 0.75rem 1.5rem;">
                                <i class="fas fa-times me-2"></i>{{ $order->status }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    @if ($order->status == 'Booking' || $order->status == 'Dikonfirmasi')
                        <form action="{{ route('guards.updateStatus', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="Sedang Mendaki">
                            <button type="submit" class="btn btn-modern btn-success-modern btn-lg"
                                onclick="return confirm('Konfirmasi Check-In pendaki?')">
                                <i class="fas fa-sign-in-alt"></i> CHECK IN
                            </button>
                        </form>
                    @elseif($order->status == 'Sedang Mendaki')
                        <form action="{{ route('guards.updateStatus', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="Selesai">
                            <button type="submit" class="btn btn-modern btn-info-modern btn-lg"
                                onclick="return confirm('Konfirmasi Check-Out pendaki?')">
                                <i class="fas fa-sign-out-alt"></i> CHECK OUT
                            </button>
                        </form>
                    @elseif($order->status == 'Selesai')
                        <button class="btn btn-modern btn-lg" style="background: #e2e8f0; color: #64748b;" disabled>
                            <i class="fas fa-check-circle"></i> Pendakian Selesai
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informasi Pesanan -->
        <div class="col-lg-6">
            <div class="modern-card animate-fade-in" style="animation-delay: 0.1s">
                <div class="card-header">
                    <h5><i class="fas fa-clipboard-list"></i> Informasi Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="info-row d-flex justify-content-between py-3 border-bottom">
                        <span class="text-muted">ID Pesanan</span>
                        <span class="fw-semibold"><code>#{{ $order->id }}</code></span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-3 border-bottom">
                        <span class="text-muted">Tanggal Booking</span>
                        <span
                            class="fw-semibold">{{ \Carbon\Carbon::parse($order->tanggal_booking)->isoFormat('D MMMM Y') }}</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-3 border-bottom">
                        <span class="text-muted">Tanggal Naik</span>
                        <span class="fw-semibold">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            {{ \Carbon\Carbon::parse($order->tanggal_naik)->isoFormat('D MMMM Y') }}
                        </span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-3 border-bottom">
                        <span class="text-muted">Tanggal Turun</span>
                        <span class="fw-semibold">
                            <i class="fas fa-arrow-down text-danger me-1"></i>
                            {{ \Carbon\Carbon::parse($order->tanggal_turun)->isoFormat('D MMMM Y') }}
                        </span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-3 border-bottom">
                        <span class="text-muted">Gunung</span>
                        <span class="fw-semibold">
                            <i class="fas fa-mountain text-primary me-1"></i>
                            {{ $order->jalur->gunung->nama ?? '-' }}
                        </span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-3 border-bottom">
                        <span class="text-muted">Jalur</span>
                        <span class="fw-semibold">{{ $order->jalur->nama ?? '-' }}</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-3">
                        <span class="text-muted">Jumlah Pendaki</span>
                        <span class="badge bg-secondary rounded-pill">{{ $order->anggotaPesanan->count() }} orang</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Pembayaran -->
        <div class="col-lg-6">
            <div class="modern-card animate-fade-in" style="animation-delay: 0.2s">
                <div class="card-header">
                    <h5><i class="fas fa-credit-card"></i> Informasi Pembayaran</h5>
                </div>
                <div class="card-body">
                    @if ($order->transaksi)
                        <div class="info-row d-flex justify-content-between py-3 border-bottom">
                            <span class="text-muted">Total Bayar</span>
                            <span class="fw-bold" style="color: var(--primary-color); font-size: 1.1rem;">
                                Rp {{ number_format($order->transaksi->total_bayar, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-3 border-bottom">
                            <span class="text-muted">Metode Pembayaran</span>
                            <span class="fw-semibold">
                                <i class="fas fa-wallet me-1 text-muted"></i>
                                {{ $order->transaksi->payment_method_name ?? '-' }}
                            </span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-3 border-bottom">
                            <span class="text-muted">Status Pembayaran</span>
                            @if ($order->transaksi->status_pesanan == 'Complete')
                                <span class="badge-modern badge-done">Lunas</span>
                            @else
                                <span class="badge-modern badge-pending">{{ $order->transaksi->status_pesanan }}</span>
                            @endif
                        </div>
                        <div class="info-row d-flex justify-content-between py-3">
                            <span class="text-muted">Tanggal Pembayaran</span>
                            <span
                                class="fw-semibold">{{ \Carbon\Carbon::parse($order->transaksi->created_at)->isoFormat('D MMMM Y HH:mm') }}</span>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3"
                                style="color: var(--accent-color); opacity: 0.5;"></i>
                            <h6>Belum Ada Data Pembayaran</h6>
                            <p class="text-muted mb-0">Informasi pembayaran akan muncul setelah pendaki melakukan
                                pembayaran</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Daftar Anggota -->
        <div class="col-12">
            <div class="modern-card animate-fade-in" style="animation-delay: 0.3s">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-users me-2"></i> Daftar Anggota Pendaki & Verifikasi Wajah (eKYC)</h5>
                    <span class="badge-modern badge-done">{{ $order->anggotaPesanan->count() }} Orang</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Foto Wajah eKYC</th>
                                    <th>Status eKYC</th>
                                    <th>NIK</th>
                                    <th>Nama Pendaki</th>
                                    <th>No HP</th>
                                    <th>Alamat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->anggotaPesanan as $index => $anggota)
                                    @php
                                        // Cari user terkait jika ada
                                        $userAccount = \App\Models\User::where('nik', $anggota->nik)->orWhere('email', $order->user->email)->first();
                                        $facePhoto = $userAccount && $userAccount->face_photo_path ? asset('storage/' . $userAccount->face_photo_path) : null;
                                        $isVerified = $userAccount && $userAccount->is_face_verified;
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            @if ($index == 0)
                                                <span class="badge bg-primary rounded-pill" title="Ketua Rombongan">
                                                    <i class="fas fa-crown me-1"></i> Ketua
                                                </span>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($facePhoto)
                                                <img src="{{ $facePhoto }}" alt="Foto Wajah" class="rounded-circle border border-success border-2 shadow-sm" style="width: 48px; height: 48px; object-fit: cover; cursor: pointer;" onclick="showFaceModal('{{ $facePhoto }}', '{{ $anggota->nama }}')" title="Klik untuk memperbesar foto wajah">
                                            @else
                                                <div class="user-avatar mx-auto bg-light text-muted border" style="width: 48px; height: 48px; font-size: 1rem; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($isVerified)
                                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-pill">
                                                    <i class="fas fa-shield-alt me-1"></i> Verifikasi Wajah Valid ✅
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 rounded-pill">
                                                    <i class="fas fa-exclamation-circle me-1"></i> Belum Verifikasi ⚠️
                                                </span>
                                            @endif
                                        </td>
                                        <td><code>{{ $anggota->nik }}</code></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-medium">{{ $anggota->nama }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="tel:{{ $anggota->no_hp }}" class="text-decoration-none">
                                                <i class="fas fa-phone text-muted me-1"></i>
                                                {{ $anggota->no_hp }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                                title="{{ $anggota->alamat }}">
                                                {{ $anggota->alamat }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-user-slash fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                                                <h6>Tidak Ada Data Anggota</h6>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Zoom Foto Wajah eKYC untuk Penjaga Jalur -->
    <div class="modal fade" id="faceModal" tabindex="-1" aria-labelledby="faceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="faceModalLabel"><i class="fas fa-user-check me-2"></i> Inspeksi Foto Wajah eKYC</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <img id="modalFaceImg" src="" alt="Foto Wajah Pembeli" class="img-fluid rounded-4 shadow border border-3 border-success mb-3" style="max-height: 350px; object-fit: cover;">
                    <h5 id="modalHikerName" class="fw-bold text-dark mb-1"></h5>
                    <p class="text-success small fw-semibold"><i class="fas fa-check-circle me-1"></i> Wajah Terverifikasi via Active Liveness Check</p>
                    <p class="text-muted small mb-0">Silakan cocokkan foto di atas dengan wajah pendaki asli yang berdiri di pos pemeriksaan.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showFaceModal(imgUrl, name) {
            document.getElementById('modalFaceImg').src = imgUrl;
            document.getElementById('modalHikerName').textContent = name;
            var faceModal = new bootstrap.Modal(document.getElementById('faceModal'));
            faceModal.show();
        }
    </script>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('guards.scanner') }}" class="btn btn-modern btn-outline-modern">
            <i class="fas fa-qrcode me-1"></i> Kembali ke Scanner
        </a>
        <a href="{{ route('guards.history') }}" class="btn btn-modern btn-outline-modern">
            <i class="fas fa-history me-1"></i> Riwayat Pengunjung
        </a>
    </div>
@endsection
