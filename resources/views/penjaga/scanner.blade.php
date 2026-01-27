@extends('layouts.app')

@section('content')

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>

    <body style="background: #117958;">
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Scanner Check In/Out</h1>
                <a href="{{ route('penjaga.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <!-- Scanner QR Code dari Kamera -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold">Scan QR Code dari Kamera</h6>
                        </div>
                        <div class="card-body">
                            <div id="reader" style="width: 100%;"></div>
                            <div id="scan-result" class="mt-3"></div>
                        </div>
                    </div>

                    <!-- Upload QR Code Image -->
                    <div class="card shadow mt-4">
                        <div class="card-header py-3 bg-warning text-white">
                            <h6 class="m-0 font-weight-bold">Scan dari File/Gambar QR Code</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Upload Gambar QR Code</label>
                                <input type="file" id="qr-input-file" accept="image/*" class="form-control-file">
                            </div>
                            <div id="file-scan-result" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                <!-- Manual Input -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-success text-white">
                            <h6 class="m-0 font-weight-bold">Input Manual ID Pesanan</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('penjaga.scanner.manual') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label class="font-weight-bold">ID Pesanan</label>
                                    <input type="text" name="pesanan_id" class="form-control"
                                        placeholder="Masukkan ID Pesanan" required>
                                </div>
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-search"></i> Cari Pesanan
                                </button>
                            </form>

                            <hr class="my-4">

                            <div class="alert alert-info">
                                <h6 class="font-weight-bold">Cara Menggunakan:</h6>
                                <ol class="mb-0">
                                    <li>Scan QR code dari kamera</li>
                                    <li>Upload gambar QR code</li>
                                    <li>Atau masukkan ID pesanan secara manual</li>
                                    <li>Sistem akan menampilkan detail pesanan</li>
                                    <li>Lakukan check-in saat pendaki datang</li>
                                    <li>Lakukan check-out saat pendaki turun</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script>
            let html5QrcodeScanner;

            function onScanSuccess(decodedText, decodedResult) {
                console.log(`Code matched = ${decodedText}`, decodedResult);

                // Stop scanner
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.clear().then(() => {
                        redirectToDetail(decodedText);
                    }).catch(err => {
                        console.error('Error stopping scanner:', err);
                        redirectToDetail(decodedText);
                    });
                } else {
                    redirectToDetail(decodedText);
                }
            }

            function redirectToDetail(pesananId) {
                window.location.href = `/penjaga/scanner/detail/${pesananId}`;
            }

            function onScanFailure(error) {
                // Silent error - normal untuk frame yang gagal decode
            }

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

            // File upload scanner
            const fileInput = document.getElementById('qr-input-file');
            const fileScanResult = document.getElementById('file-scan-result');

            fileInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (!file) {
                    return;
                }

                fileScanResult.innerHTML = '<div class="alert alert-info">Memproses gambar...</div>';

                const html5QrcodeFile = new Html5Qrcode("file-scan-result");

                html5QrcodeFile.scanFile(file, true)
                    .then(decodedText => {
                        fileScanResult.innerHTML = `
                            <div class="alert alert-success">
                                <strong>Berhasil!</strong> ID Pesanan: ${decodedText}
                                <button class="btn btn-sm btn-primary mt-2 btn-block" onclick="redirectToDetail('${decodedText}')">
                                    Lihat Detail Pesanan
                                </button>
                            </div>
                        `;
                    })
                    .catch(err => {
                        fileScanResult.innerHTML = `
                            <div class="alert alert-danger">
                                <strong>Error!</strong> Tidak dapat membaca QR code dari gambar. 
                                Pastikan gambar jelas dan berisi QR code yang valid.
                            </div>
                        `;
                        console.error('Error scanning file:', err);
                    });
            });
        </script>
    </body>
@endsection
