@extends('layouts.admin-modern')

@section('main-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Ajukan Penarikan Saldo</h4>
                <p class="text-muted mb-0">Ajukan pencairan saldo pendapatan Anda dengan perhitungan fee yang transparan.</p>
            </div>
            <a href="{{ route('trail-guard.withdrawal.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="mb-0">Form Permintaan Penarikan</h5>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="fw-semibold mb-2">Data belum lengkap</div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('trail-guard.withdrawal.store') }}" id="withdrawalForm">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Jumlah Penarikan <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg mb-2">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="amount" class="form-control" min="10000" step="1000"
                                        placeholder="Masukkan jumlah penarikan" required onchange="calculateFee()"
                                        oninput="calculateFee()" value="{{ old('amount') }}">
                                </div>
                                <div class="small text-muted">Minimal Rp 10.000 dan tidak boleh melebihi saldo tersedia.</div>
                            </div>

                            <div class="alert alert-info border-0 shadow-sm">
                                <div class="fw-semibold mb-1">Penting</div>
                                <div class="small">
                                    Fee admin akan dipotong dari jumlah penarikan Anda dan dicatat sebagai pendapatan sistem admin.
                                    Karena itu, saldo Anda akan berkurang sebesar <strong>jumlah request penuh</strong>, bukan hanya dana bersih yang diterima.
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Metode Penarikan <span class="text-danger">*</span></label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="border rounded-3 p-3 w-100 h-100" for="bankTransfer" style="cursor: pointer;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="withdrawal_method"
                                                    id="bankTransfer" value="bank_transfer"
                                                    {{ old('withdrawal_method') === 'bank_transfer' ? 'checked' : '' }}
                                                    onchange="toggleWithdrawalMethod()" required>
                                                <span class="form-check-label fw-semibold">Bank Transfer</span>
                                            </div>
                                            <small class="text-muted d-block mt-2">Transfer langsung ke rekening bank Anda.</small>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="border rounded-3 p-3 w-100 h-100" for="eWallet" style="cursor: pointer;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="withdrawal_method"
                                                    id="eWallet" value="e_wallet"
                                                    {{ old('withdrawal_method') === 'e_wallet' ? 'checked' : '' }}
                                                    onchange="toggleWithdrawalMethod()" required>
                                                <span class="form-check-label fw-semibold">E-Wallet</span>
                                            </div>
                                            <small class="text-muted d-block mt-2">Transfer ke akun e-wallet Anda.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div id="bankSection" class="mb-4">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="mb-3"><i class="bx bx-building text-info me-2"></i>Detail Bank</h6>
                                        <div class="mb-3">
                                            <label class="form-label">Nama Bank <span class="text-danger">*</span></label>
                                            <input type="text" name="bank_name" class="form-control"
                                                placeholder="Contoh: Bank BCA, Bank Mandiri" value="{{ old('bank_name') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nama Pemegang Rekening <span class="text-danger">*</span></label>
                                            <input type="text" name="account_holder" class="form-control"
                                                placeholder="Sesuai nama pada rekening" value="{{ old('account_holder') }}">
                                        </div>
                                        <div>
                                            <label class="form-label">Nomor Rekening <span class="text-danger">*</span></label>
                                            <input type="text" name="account_number" class="form-control"
                                                placeholder="Contoh: 1234567890" value="{{ old('account_number') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="eWalletSection" class="mb-4" style="display: none;">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="mb-3"><i class="bx bx-mobile text-warning me-2"></i>Detail E-Wallet</h6>
                                        <div class="mb-3">
                                            <label class="form-label">Tipe E-Wallet <span class="text-danger">*</span></label>
                                            <select name="e_wallet_type" class="form-select">
                                                <option value="">Pilih E-Wallet</option>
                                                <option value="gcash" {{ old('e_wallet_type') === 'gcash' ? 'selected' : '' }}>GCash</option>
                                                <option value="grab" {{ old('e_wallet_type') === 'grab' ? 'selected' : '' }}>Grab</option>
                                                <option value="linkaja" {{ old('e_wallet_type') === 'linkaja' ? 'selected' : '' }}>LinkAja</option>
                                                <option value="ovo" {{ old('e_wallet_type') === 'ovo' ? 'selected' : '' }}>OVO</option>
                                                <option value="dana" {{ old('e_wallet_type') === 'dana' ? 'selected' : '' }}>DANA</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label">Nomor E-Wallet <span class="text-danger">*</span></label>
                                            <input type="text" name="e_wallet_number" class="form-control"
                                                placeholder="Contoh: 0812xxxx" value="{{ old('e_wallet_number') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-send me-2"></i>Ajukan Permintaan
                                </button>
                                <a href="{{ route('trail-guard.withdrawal.index') }}" class="btn btn-outline-secondary">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="mb-0">Ringkasan Saldo</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Total Pendapatan Kotor</div>
                            <div class="fw-semibold fs-5">Rp {{ number_format($user->total_earnings ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Total Saldo Sudah Diproses</div>
                            <div class="fw-semibold fs-5 text-success">Rp {{ number_format($user->withdrawn_amount ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Total Fee Admin Terpotong</div>
                            <div class="fw-semibold fs-5 text-warning">Rp {{ number_format($withdrawalSummary['admin_fee_paid'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="bg-primary bg-opacity-10 border border-primary p-3 rounded-3">
                            <div class="text-primary mb-1">Saldo Tersedia untuk Ditarik</div>
                            <div class="fw-bold fs-4 text-primary">Rp {{ number_format($user->available_balance ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="mb-0">Perhitungan Biaya</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Jumlah Request</span>
                            <strong id="displayAmount">Rp 0</strong>
                        </div>

                        @if ($adminFeeSettings->fee_type === 'percentage' || $adminFeeSettings->fee_type === 'both')
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Fee Admin ({{ $adminFeeSettings->fee_percentage }}%)</span>
                                <strong id="displayPercentageFee">Rp 0</strong>
                            </div>
                        @endif

                        @if ($adminFeeSettings->fee_type === 'fixed' || $adminFeeSettings->fee_type === 'both')
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Fee Admin Tetap</span>
                                <strong id="displayFixedFee">Rp {{ number_format($adminFeeSettings->fixed_fee, 0, ',', '.') }}</strong>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">Total Fee Admin</span>
                            <strong id="displayTotalFee" class="text-warning">Rp 0</strong>
                        </div>

                        <div class="rounded-3 border p-3 mb-3">
                            <small class="text-muted d-block mb-1">Dana Bersih yang Anda Terima</small>
                            <div class="fw-bold fs-5 text-success" id="displayNetAmount">Rp 0</div>
                        </div>

                        <div class="rounded-3 bg-light border p-3">
                            <small class="text-muted d-block mb-1">Total Saldo yang Akan Dipotong dari Akun Anda</small>
                            <div class="fw-bold fs-5 text-danger" id="displayProcessedAmount">Rp 0</div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="mb-0">Informasi Penting</h5>
                    </div>
                    <div class="card-body">
                        <ul class="small ps-3 mb-0">
                            <li class="mb-2">Pastikan rekening atau e-wallet yang Anda isi benar.</li>
                            <li class="mb-2">Permintaan biasanya diproses dalam 1-3 hari kerja.</li>
                            <li class="mb-2">Fee admin masuk ke laporan penghasilan sistem admin.</li>
                            <li>Saldo Anda berkurang berdasarkan jumlah request penuh ketika status withdrawal selesai.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleWithdrawalMethod() {
            const selectedMethod = document.querySelector('input[name="withdrawal_method"]:checked');
            if (!selectedMethod) {
                document.getElementById('bankSection').style.display = 'none';
                document.getElementById('eWalletSection').style.display = 'none';
                return;
            }

            const method = selectedMethod.value;
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

            const processedAmount = amount;
            const netAmount = Math.max(0, amount - totalFee);

            document.getElementById('displayAmount').textContent = 'Rp ' + Math.round(amount).toLocaleString('id-ID');
            document.getElementById('displayTotalFee').textContent = 'Rp ' + Math.round(totalFee).toLocaleString('id-ID');
            document.getElementById('displayNetAmount').textContent = 'Rp ' + Math.round(netAmount).toLocaleString('id-ID');
            document.getElementById('displayProcessedAmount').textContent = 'Rp ' + Math.round(processedAmount).toLocaleString('id-ID');

            if (feeType === 'percentage' || feeType === 'both') {
                const percentageFee = (amount * feePercentage) / 100;
                const percentageElement = document.getElementById('displayPercentageFee');
                if (percentageElement) {
                    percentageElement.textContent = 'Rp ' + Math.round(percentageFee).toLocaleString('id-ID');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleWithdrawalMethod();
            calculateFee();
        });
    </script>
@endsection
