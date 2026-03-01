<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Selesai - My Hiking</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #127857 0%, #0a5c42 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 24px;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }
        
        .icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 40px;
        }
        
        .icon.success {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
        }
        
        .icon.pending {
            background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%);
            color: white;
        }
        
        .icon.failed {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 12px;
        }
        
        p {
            color: #7f8c8d;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        
        .order-id {
            background: #f8f9fa;
            padding: 12px 20px;
            border-radius: 12px;
            font-family: monospace;
            font-size: 14px;
            color: #34495e;
            margin-bottom: 24px;
        }
        
        .btn {
            background: linear-gradient(135deg, #127857 0%, #0a5c42 100%);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(18, 120, 87, 0.3);
        }
        
        .note {
            margin-top: 20px;
            font-size: 13px;
            color: #95a5a6;
        }
    </style>
</head>
<body>
    <div class="container">
        @if($status === 'settlement' || $status === 'capture')
            <div class="icon success">✓</div>
            <h1>Pembayaran Berhasil!</h1>
            <p>Terima kasih! Pembayaran Anda telah berhasil diproses. Tiket pendakian Anda sudah aktif.</p>
        @elseif($status === 'pending')
            <div class="icon pending">⏳</div>
            <h1>Menunggu Pembayaran</h1>
            <p>Silakan selesaikan pembayaran Anda sesuai instruksi yang diberikan.</p>
        @else
            <div class="icon failed">✕</div>
            <h1>Pembayaran Gagal</h1>
            <p>Maaf, pembayaran Anda tidak dapat diproses. Silakan coba lagi atau gunakan metode pembayaran lain.</p>
        @endif
        
        @if($orderId)
            <div class="order-id">
                Order ID: {{ $orderId }}
            </div>
        @endif
        
        <button class="btn" onclick="closeWebview()">
            Kembali ke Aplikasi
        </button>
        
        <p class="note">
            Halaman ini akan otomatis tertutup dalam beberapa detik
        </p>
    </div>
    
    <script>
        function closeWebview() {
            // Try multiple methods to close webview
            if (window.flutter_inappwebview) {
                window.flutter_inappwebview.callHandler('closeWebview');
            } else if (window.FlutterChannel) {
                window.FlutterChannel.postMessage('close');
            } else {
                // Fallback: try to close window
                window.close();
            }
        }
        
        // Auto close after 5 seconds for success
        @if($status === 'settlement' || $status === 'capture')
        setTimeout(function() {
            closeWebview();
        }, 5000);
        @endif
    </script>
</body>
</html>
