@extends('layouts.admin-modern')

@section('main-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h4 class="fw-bold">Pengaturan Biaya Admin</h4>
                <p class="text-muted">Atur biaya admin untuk setiap request penarikan saldo</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.earnings.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Alerts -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h6 class="alert-heading d-flex align-items-center"><i class="bx bx-info-circle me-2"></i> Error</h6>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Settings Form -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Atur Biaya Admin</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.earnings.admin-fee-settings-update') }}"
                            id="settingsForm">
                            @csrf
                            @method('PUT')

                            <!-- Fee Type Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Tipe Biaya Admin</label>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="fee_type"
                                                id="feePercentage" value="percentage"
                                                {{ $adminFeeSettings->fee_type === 'percentage' ? 'checked' : '' }}
                                                onchange="toggleFeeType()">
                                            <label class="form-check-label" for="feePercentage">
                                                Persentase
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">Biaya berdasarkan persentase dari jumlah
                                            penarikan</small>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="fee_type" id="feeFixed"
                                                value="fixed"
                                                {{ $adminFeeSettings->fee_type === 'fixed' ? 'checked' : '' }}
                                                onchange="toggleFeeType()">
                                            <label class="form-check-label" for="feeFixed">
                                                Biaya Tetap
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">Biaya tetap untuk setiap transaksi</small>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="fee_type" id="feeBoth"
                                                value="both" {{ $adminFeeSettings->fee_type === 'both' ? 'checked' : '' }}
                                                onchange="toggleFeeType()">
                                            <label class="form-check-label" for="feeBoth">
                                                Keduanya
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">Kombinasi persentase + biaya tetap</small>
                                    </div>
                                </div>
                            </div>

                            <hr />

                            <!-- Percentage Fee -->
                            <div class="mb-4" id="percentageSection">
                                <label class="form-label fw-semibold">Persentase Biaya (%)</label>
                                <div class="input-group">
                                    <input type="number" name="fee_percentage" class="form-control" min="0"
                                        max="100" step="0.01" value="{{ $adminFeeSettings->fee_percentage }}"
                                        placeholder="Contoh: 5">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <strong>Contoh:</strong> Jika 5%, maka untuk penarikan Rp 1.000.000 biayanya Rp 50.000
                                </small>
                            </div>

                            <!-- Fixed Fee -->
                            <div class="mb-4" id="fixedSection">
                                <label class="form-label fw-semibold">Biaya Tetap per Transaksi (Rp)</label>
                                <input type="number" name="fixed_fee" class="form-control" min="0" step="100"
                                    value="{{ $adminFeeSettings->fixed_fee }}" placeholder="Contoh: 5000">
                                <small class="text-muted d-block mt-2">
                                    <strong>Contoh:</strong> Jika Rp 5.000, maka setiap transaksi dipotong Rp 5.000
                                </small>
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Deskripsi / Catatan</label>
                                <textarea name="description" class="form-control" rows="3"
                                    placeholder="Catatan tentang pengaturan biaya admin ini (opsional)...">{{ $adminFeeSettings->description }}</textarea>
                            </div>

                            <!-- Preview -->
                            <div class="alert alert-info mb-4">
                                <h6 class="alert-heading mb-3">Preview Perhitungan Biaya</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label small">Masukkan jumlah penarikan untuk preview:</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" id="previewAmount" class="form-control" min="0"
                                                step="1000" placeholder="Contoh: 1000000"
                                                onchange="calculatePreview()" oninput="calculatePreview()">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Biaya Admin yang akan dipotong:</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="previewFee" class="form-control" readonly
                                                value="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-light p-3 rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Jumlah Bersih yang diterima penjaga:</span>
                                        <strong class="text-success" id="previewNet">Rp 0</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-2"></i> Simpan Pengaturan
                                </button>
                                <a href="{{ route('admin.earnings.index') }}" class="btn btn-secondary">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="col-lg-4">
                <!-- Current Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Pengaturan Saat Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Tipe Biaya</h6>
                            <div class="badge bg-primary fs-6">
                                {{ $adminFeeSettings->getFeeTypeLabel() }}
                            </div>
                        </div>

                        @if ($adminFeeSettings->fee_type === 'percentage' || $adminFeeSettings->fee_type === 'both')
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Persentase Biaya</h6>
                                <p class="fw-semibold mb-0">{{ $adminFeeSettings->fee_percentage }}%</p>
                            </div>
                        @endif

                        @if ($adminFeeSettings->fee_type === 'fixed' || $adminFeeSettings->fee_type === 'both')
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Biaya Tetap</h6>
                                <p class="fw-semibold mb-0">Rp
                                    {{ number_format($adminFeeSettings->fixed_fee, 0, ',', '.') }}</p>
                            </div>
                        @endif

                        <hr />

                        <h6 class="text-muted mb-2">Terakhir Diperbarui</h6>
                        <small>{{ $adminFeeSettings->updated_at->format('d M Y, H:i:s') }}</small>
                    </div>
                </div>

                <!-- Information Card -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Informasi Penting</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3">Penjelasan Tipe Biaya:</h6>
                        <ul class="list-unstyled small">
                            <li class="mb-3">
                                <strong class="d-block mb-1">📊 Persentase</strong>
                                Biaya dihitung berdasarkan persentase dari jumlah penarikan. Cocok untuk sistem yang
                                fleksibel.
                            </li>
                            <li class="mb-3">
                                <strong class="d-block mb-1">💰 Biaya Tetap</strong>
                                Biaya yang sama untuk setiap transaksi. Cocok untuk biaya administrasi standar.
                            </li>
                            <li>
                                <strong class="d-block mb-1">🔀 Keduanya</strong>
                                Kombinasi persentase dan biaya tetap. Contoh: 5% + Rp 5.000 per transaksi.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleFeeType() {
            const feeType = document.querySelector('input[name="fee_type"]:checked').value;

            const percentageSection = document.getElementById('percentageSection');
            const fixedSection = document.getElementById('fixedSection');

            percentageSection.style.display = (feeType === 'percentage' || feeType === 'both') ? 'block' : 'none';
            fixedSection.style.display = (feeType === 'fixed' || feeType === 'both') ? 'block' : 'none';
        }

        function calculatePreview() {
            const amount = parseFloat(document.getElementById('previewAmount').value) || 0;
            const feeType = document.querySelector('input[name="fee_type"]:checked').value;
            const feePercentage = parseFloat(document.querySelector('input[name="fee_percentage"]').value) || 0;
            const fixedFee = parseFloat(document.querySelector('input[name="fixed_fee"]').value) || 0;

            let totalFee = 0;

            if (feeType === 'percentage' || feeType === 'both') {
                totalFee += (amount * feePercentage) / 100;
            }

            if (feeType === 'fixed' || feeType === 'both') {
                totalFee += fixedFee;
            }

            const netAmount = amount - totalFee;

            document.getElementById('previewFee').value = Math.round(totalFee).toLocaleString('id-ID');
            document.getElementById('previewNet').textContent = 'Rp ' + Math.round(netAmount).toLocaleString('id-ID');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            toggleFeeType();
        });
    </script>
@endsection
