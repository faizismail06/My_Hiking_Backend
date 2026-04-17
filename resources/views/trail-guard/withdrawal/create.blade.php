@extends('layouts.admin-modern')

@section('main-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h4 class="fw-bold">Ajukan Penarikan Saldo</h4>
                <p class="text-muted">Ajukan permintaan penarikan saldo pendapatan Anda</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('trail-guard.withdrawal.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Form Column -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Form Permintaan Penarikan</h5>
                    </div>
                    <div class="card-body">
                        <!-- Alerts -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading d-flex align-items-center"><i class="bx bx-info-circle me-2"></i>
                                    Error</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('trail-guard.withdrawal.store') }}" id="withdrawalForm">
                            @csrf

                            <!-- Amount Section -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Jumlah Penarikan <span
                                        class="text-danger">*</span></label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="amount" class="form-control form-control-lg" min="10000"
                                        step="1000" placeholder="Masukkan jumlah penarikan" required
                                        onchange="calculateFee()" oninput="calculateFee()" value="{{ old('amount') }}">
                                </div>
                                <small class="text-muted">Minimum Rp 10.000</small>
                            </div>

                            <hr />

                            <!-- Withdrawal Method -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Metode Penarikan <span
                                        class="text-danger">*</span></label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="withdrawal_method"
                                                id="bankTransfer" value="bank_transfer"
                                                {{ old('withdrawal_method') === 'bank_transfer' ? 'checked' : '' }}
                                                onchange="toggleWithdrawalMethod()" required>
                                            <label class="form-check-label" for="bankTransfer">
                                                <strong>Bank Transfer</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">Transfer langsung ke rekening bank
                                            Anda</small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="withdrawal_method"
                                                id="eWallet" value="e_wallet"
                                                {{ old('withdrawal_method') === 'e_wallet' ? 'checked' : '' }}
                                                onchange="toggleWithdrawalMethod()" required>
                                            <label class="form-check-label" for="eWallet">
                                                <strong>E-Wallet</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">Transfer ke aplikasi e-wallet Anda</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Transfer Details -->
                            <div id="bankSection" class="mb-4">
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="mb-3"><i class="bx bx-building text-info me-2"></i> Detail Bank</h6>

                                        <div class="mb-3">
                                            <label class="form-label">Nama Bank <span class="text-danger">*</span></label>
                                            <input type="text" name="bank_name" class="form-control"
                                                placeholder="Contoh: Bank BCA, Bank Mandiri, Bank BNI"
                                                value="{{ old('bank_name') }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Nama Pemegang Rekening <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="account_holder" class="form-control"
                                                placeholder="Sesuai dengan rekening bank Anda"
                                                value="{{ old('account_holder') }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Nomor Rekening <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="account_number" class="form-control"
                                                placeholder="Contoh: 1234567890" value="{{ old('account_number') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- E-Wallet Details -->
                            <div id="eWalletSection" class="mb-4" style="display: none;">
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="mb-3"><i class="bx bx-mobile text-warning me-2"></i> Detail E-Wallet
                                        </h6>

                                        <div class="mb-3">
                                            <label class="form-label">Tipe E-Wallet <span
                                                    class="text-danger">*</span></label>
                                            <select name="e_wallet_type" class="form-select">
                                                <option value="">Pilih E-Wallet</option>
                                                <option value="gcash"
                                                    {{ old('e_wallet_type') === 'gcash' ? 'selected' : '' }}>GCash</option>
                                                <option value="grab"
                                                    {{ old('e_wallet_type') === 'grab' ? 'selected' : '' }}>Grab</option>
                                                <option value="linkaja"
                                                    {{ old('e_wallet_type') === 'linkaja' ? 'selected' : '' }}>LINKAJA
                                                </option>
                                                <option value="ovo"
                                                    {{ old('e_wallet_type') === 'ovo' ? 'selected' : '' }}>OVO</option>
                                                <option value="dana"
                                                    {{ old('e_wallet_type') === 'dana' ? 'selected' : '' }}>DANA</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Nomor E-Wallet <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="e_wallet_number" class="form-control"
                                                placeholder="Contoh: 0812xxxx" value="{{ old('e_wallet_number') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-send me-2"></i> Ajukan Permintaan
                                </button>
                                <a href="{{ route('trail-guard.withdrawal.index') }}" class="btn btn-secondary">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Summary Column -->
            <div class="col-lg-4">
                <!-- Balance Summary -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Ringkasan Saldo</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Total Pendapatan</h6>
                            <p class="fw-semibold fs-5">
                                Rp {{ number_format($user->total_earnings ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Sudah Dicairkan</h6>
                            <p class="fw-semibold fs-5 text-success">
                                Rp {{ number_format($user->withdrawn_amount ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="bg-primary bg-opacity-10 border border-primary p-3 rounded">
                            <h6 class="mb-1 text-primary">Saldo Tersedia untuk Ditarik</h6>
                            <p class="fw-bold fs-5 mb-0 text-primary">
                                Rp {{ number_format($user->available_balance ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fee Calculation -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Perhitungan Biaya</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Jumlah Request</span>
                                <strong id="displayAmount">Rp 0</strong>
                            </div>

                            @if ($adminFeeSettings->fee_type === 'percentage' || $adminFeeSettings->fee_type === 'both')
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Biaya Admin ({{ $adminFeeSettings->fee_percentage }}%)</span>
                                    <strong id="displayPercentageFee">Rp 0</strong>
                                </div>
                            @endif

                            @if ($adminFeeSettings->fee_type === 'fixed' || $adminFeeSettings->fee_type === 'both')
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Biaya Admin (Tetap)</span>
                                    <strong id="displayFixedFee">Rp
                                        {{ number_format($adminFeeSettings->fixed_fee, 0, ',', '.') }}</strong>
                                </div>
                            @endif

                            <hr />

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">Total Biaya Admin</span>
                                <strong id="displayTotalFee" class="text-danger">Rp 0</strong>
                            </div>
                        </div>

                        <div class="bg-success bg-opacity-10 border border-success p-3 rounded">
                            <small class="text-muted d-block mb-1">Jumlah Bersih yang Anda Terima</small>
                            <p class="fw-bold fs-5 mb-0 text-success">
                                <span id="displayNetAmount">Rp 0</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Information -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Informasi Penting</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <strong class="d-block mb-1">✓ Verifikasi Data</strong>
                                Pastikan data rekening atau e-wallet yang Anda masukkan benar.
                            </li>
                            <li class="mb-2">
                                <strong class="d-block mb-1">⏱️ Waktu Pemrosesan</strong>
                                Permintaan Anda akan diproses dalam 1-3 hari kerja.
                            </li>
                            <li>
                                <strong class="d-block mb-1">💡 Biaya Admin</strong>
                                Biaya admin akan dipotong dari jumlah penarikan Anda.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleWithdrawalMethod() {
            const method = document.querySelector('input[name="withdrawal_method"]:checked').value;

            document.getElementById('bankSection').style.display = method === 'bank_transfer' ? 'block' : 'none';
            document.getElementById('eWalletSection').style.display = method === 'e_wallet' ? 'block' : 'none';
        }

        function calculateFee() {
            const amount = parseFloat(document.querySelector('input[name="amount"]').value) || 0;
            const feePercentage = {{ $adminFeeSettings->fee_percentage }};
            const fixedFee = {{ $adminFeeSettings->fixed_fee }};
            const feeType = '{{ $adminFeeSettings->fee_type }}';

            let totalFee = 0;

            if (feeType === 'percentage' || feeType === 'both') {
                totalFee += (amount * feePercentage) / 100;
            }

            if (feeType === 'fixed' || feeType === 'both') {
                totalFee += fixedFee;
            }

            const netAmount = amount - totalFee;

            // Update display
            document.getElementById('displayAmount').textContent = 'Rp ' + Math.round(amount).toLocaleString('id-ID');
            document.getElementById('displayTotalFee').textContent = 'Rp ' + Math.round(totalFee).toLocaleString('id-ID');
            document.getElementById('displayNetAmount').textContent = 'Rp ' + Math.round(netAmount).toLocaleString('id-ID');

            // Update percentage fee if applicable
            if (feeType === 'percentage' || feeType === 'both') {
                const percentageFee = (amount * feePercentage) / 100;
                document.getElementById('displayPercentageFee').textContent = 'Rp ' + Math.round(percentageFee)
                    .toLocaleString('id-ID');
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            toggleWithdrawalMethod();
            calculateFee();
        });
    </script>
@endsection
