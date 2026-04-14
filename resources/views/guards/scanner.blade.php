@extends('layouts.guards')

@section('page-title', 'Scanner QR')
@section('page-subtitle', 'Scan QR code untuk check-in/out pendaki')

@push('styles')
    <style>
        .scanner-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .scanner-card .scanner-header {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .scanner-card .scanner-header.camera {
            background: linear-gradient(135deg, var(--info-color) 0%, #2980b9 100%);
        }

        .scanner-card .scanner-header.upload {
            background: linear-gradient(135deg, var(--accent-color) 0%, #e67e22 100%);
        }

        .scanner-card .scanner-header.manual {
            background: linear-gradient(135deg, var(--success-color) 0%, #1e8449 100%);
        }

        .scanner-card .scanner-header i {
            font-size: 1.5rem;
            color: white;
        }

        .scanner-card .scanner-header h6 {
            margin: 0;
            color: white;
            font-weight: 600;
        }

        .scanner-card .scanner-body {
            padding: 1.5rem;
        }

        #reader {
            border-radius: 12px;
            overflow: hidden;
        }

        #reader video {
            border-radius: 12px;
        }

        .upload-zone {
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #f8fafc;
        }

        .upload-zone:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }

        .upload-zone i {
            font-size: 3rem;
            color: #94a3b8;
            margin-bottom: 1rem;
            display: block;
        }

        .info-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #bae6fd;
        }

        .info-card h6 {
            color: #0369a1;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .info-card ol {
            color: #0c4a6e;
            margin: 0;
            padding-left: 1.25rem;
        }

        .info-card ol li {
            margin-bottom: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="row g-4">
        <!-- Camera Scanner -->
        <div class="col-lg-6">
            <div class="scanner-card animate-fade-in">
                <div class="scanner-header camera">
                    <i class="fas fa-camera"></i>
                    <h6>Scan dari Kamera</h6>
                </div>
                <div class="scanner-body">
                    <div id="reader"></div>
                    <div id="scan-result" class="mt-3"></div>
                </div>
            </div>

            <!-- Upload QR -->
            <div class="scanner-card mt-4 animate-fade-in" style="animation-delay: 0.1s">
                <div class="scanner-header upload">
                    <i class="fas fa-upload"></i>
                    <h6>Upload Gambar QR</h6>
                </div>
                <div class="scanner-body">
                    <label class="upload-zone w-100 mb-0" for="qr-input-file">
                        <input type="file" id="qr-input-file" accept="image/*" class="d-none">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p class="mb-1 fw-medium">Klik untuk upload gambar QR code</p>
                        <small class="text-muted">JPG, PNG</small>
                    </label>
                    <div id="file-scan-result" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Manual Input & Help -->
        <div class="col-lg-6">
            <div class="scanner-card animate-fade-in" style="animation-delay: 0.2s">
                <div class="scanner-header manual">
                    <i class="fas fa-keyboard"></i>
                    <h6>Input Manual</h6>
                </div>
                <div class="scanner-body">
                    <form action="{{ route('guards.scanner.manual') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label-modern">ID Pesanan</label>
                            <div class="input-group">
                                <span class="input-group-text"
                                    style="border-radius: 10px 0 0 10px; border: 1px solid #e2e8f0;">
                                    <i class="fas fa-hashtag text-muted"></i>
                                </span>
                                <input type="text" name="pesanan_id" class="form-control form-modern"
                                    style="border-radius: 0 10px 10px 0;" placeholder="Masukkan ID Pesanan" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-modern btn-success-modern w-100">
                            <i class="fas fa-search"></i> Cari Pesanan
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="info-card">
                        <h6><i class="fas fa-info-circle me-2"></i>Cara Menggunakan</h6>
                        <ol>
                            <li>Scan QR code dari kamera perangkat</li>
                            <li>Atau upload gambar QR code</li>
                            <li>Atau masukkan ID pesanan manual</li>
                            <li>Sistem akan menampilkan detail pesanan</li>
                            <li><strong>Check In</strong> saat pendaki datang</li>
                            <li><strong>Check Out</strong> saat pendaki turun</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        let html5QrcodeScanner;
        let scanInProgress = false;

        function onScanSuccess(decodedText, decodedResult) {
            if (scanInProgress) return; // Prevent multiple scans
            scanInProgress = true;

            console.log(`Code matched = ${decodedText}`, decodedResult);

            // Stop scanner
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().then(() => {
                    autoScanOrder(decodedText);
                }).catch(err => {
                    console.error('Error stopping scanner:', err);
                    autoScanOrder(decodedText);
                });
            } else {
                autoScanOrder(decodedText);
            }
        }

        function autoScanOrder(pesananId) {
            // Show loading alert
            const scanResult = document.getElementById('scan-result');
            scanResult.innerHTML = `
                <div class="alert alert-modern alert-info">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Memproses scan pesanan...
                </div>
            `;

            // Call auto-scan endpoint
            fetch(`/guards/scanner/auto-scan/${pesananId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Error processing scan');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        scanResult.innerHTML = `
                        <div class="alert alert-modern alert-success alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-check-circle"></i>
                                <strong>Berhasil!</strong>
                            </div>
                            <p class="mb-2">${data.message}</p>
                            <small class="text-muted">Status: <span class="badge bg-success">${data.new_status}</span></small>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;

                        // Re-initialize scanner after 2 seconds
                        setTimeout(() => {
                            scanInProgress = false;
                            scanResult.innerHTML = '';
                            html5QrcodeScanner = new Html5QrcodeScanner(
                                "reader", {
                                    fps: 10,
                                    qrbox: {
                                        width: 250,
                                        height: 250
                                    },
                                    formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
                                },
                                false
                            );
                            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                        }, 2000);
                    } else {
                        scanResult.innerHTML = `
                        <div class="alert alert-modern alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Error!</strong>
                            </div>
                            <p class="mb-0">${data.message}</p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;

                        // Restart scanner
                        setTimeout(() => {
                            scanInProgress = false;
                            initializeScanner();
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    scanResult.innerHTML = `
                    <div class="alert alert-modern alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Error!</strong>
                        </div>
                        <p class="mb-0">${error.message}</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;

                    // Restart scanner
                    setTimeout(() => {
                        scanInProgress = false;
                        initializeScanner();
                    }, 3000);
                });
        }

        function redirectToDetail(pesananId) {
            window.location.href = `/guards/scanner/detail/${pesananId}`;
        }

        function onScanFailure(error) {
            // Silent error - normal untuk frame yang gagal decode
        }

        function initializeScanner() {
            // Configuration untuk camera scanner
            const config = {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                },
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
            };

            // Initialize camera scanner
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                config,
                false // verbose
            );

            // Render camera scanner
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }

        // Initialize scanner on page load
        initializeScanner();

        // File upload scanner
        const fileInput = document.getElementById('qr-input-file');
        const fileScanResult = document.getElementById('file-scan-result');

        fileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (!file) {
                return;
            }

            fileScanResult.innerHTML = '<div class="alert alert-modern alert-info">Memproses gambar...</div>';

            const html5QrcodeFile = new Html5Qrcode("file-scan-result");

            html5QrcodeFile.scanFile(file, true)
                .then(decodedText => {
                    fileScanResult.innerHTML = `
                        <div class="alert alert-modern alert-success">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-check-circle"></i>
                                <strong>Berhasil!</strong>
                            </div>
                            <p class="mb-2">ID Pesanan: <code>${decodedText}</code></p>
                            <button class="btn btn-modern btn-primary-modern w-100" onclick="autoScanOrder('${decodedText}')">
                                <i class="fas fa-arrow-right me-1"></i> Proses Auto-Scan
                            </button>
                        </div>
                    `;
                })
                .catch(err => {
                    fileScanResult.innerHTML = `
                        <div class="alert alert-modern alert-danger">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Gagal!</strong>
                            </div>
                            <p class="mb-0">Tidak dapat membaca QR code dari gambar. Pastikan gambar jelas dan berisi QR code yang valid.</p>
                        </div>
                    `;
                    console.error('Error scanning file:', err);
                });
        });
    </script>
@endpush
