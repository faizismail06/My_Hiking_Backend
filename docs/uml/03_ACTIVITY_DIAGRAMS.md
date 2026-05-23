# Activity Diagrams

## 1. Booking Tiket + Validasi DSS

```mermaid
flowchart TD
    subgraph Pendaki
        A1[Memilih gunung, jalur, tanggal, dan anggota]
        A2[Mengirim permintaan booking]
        A9[Konfirmasi lanjut jika risiko tinggi]
    end

    subgraph MobileApp["Mobile App"]
        B1[Validasi input form]
        B2[POST /api/orders]
        B9[POST /api/orders dengan force_continue]
    end

    subgraph LaravelBackend["Laravel Backend"]
        C1[Middleware auth:sanctum dan hiker.ready]
        C2[OrderController validasi request]
        C3[Cek relasi gunung dan jalur]
        C4[Cek kuota harian jalur]
        C5[Evaluasi DSS untuk pendaki level 1]
        C6{Risiko tinggi?}
        C7[Buat Order dan OrderMember]
        C8[Kirim response order]
    end

    subgraph Database
        D1[(users)]
        D2[(routes)]
        D3[(orders)]
        D4[(order_members)]
    end

    subgraph ExternalService["External Service"]
        E1[OpenWeather API]
    end

    A1 --> B1 --> A2 --> B2 --> C1
    C1 --> D1
    C1 --> C2 --> C3 --> D2
    C3 --> C4 --> D3
    C4 --> C5 --> E1
    C5 --> C6
    C6 -- Ya, belum konfirmasi --> C8
    C8 --> A9 --> B9 --> C1
    C6 -- Tidak atau sudah konfirmasi --> C7
    C7 --> D3
    C7 --> D4
    C7 --> C8
```

## 2. Pembayaran Midtrans

```mermaid
flowchart TD
    subgraph Pendaki
        A1[Memilih Bayar Sekarang]
        A2[Menyelesaikan pembayaran]
    end

    subgraph MobileApp["Mobile App"]
        B1[POST /api/midtrans/create-payment]
        B2[Tampilkan instruksi, QRIS, deeplink, atau Snap]
        B3[Polling GET /api/payment/status/{orderId}]
    end

    subgraph LaravelBackend["Laravel Backend"]
        C1[MidtransController createPayment]
        C2[Hitung total pembayaran]
        C3[Create/update Transaction]
        C4[Handle callback notification]
        C5[Verifikasi signature]
        C6[Update status transaksi dan order]
    end

    subgraph Database
        D1[(orders)]
        D2[(transactions)]
    end

    subgraph ExternalService["External Service"]
        E1[Midtrans Direct Charge atau Snap]
        E2[Midtrans Notification]
    end

    A1 --> B1 --> C1
    C1 --> D1 --> C2
    C2 --> E1 --> C3 --> D2
    C3 --> B2 --> A2
    A2 --> E2 --> C4 --> C5
    C5 --> C6 --> D2
    C6 --> D1
    B2 --> B3 --> C6
```

## 3. Rekomendasi DSS TOPSIS

```mermaid
flowchart TD
    subgraph Pendaki
        A1[Mengisi preferensi DSS]
        A2[Meminta rekomendasi]
        A3[Melihat ranking jalur]
    end

    subgraph MobileApp["Mobile App"]
        B1[GET /api/recommendations]
    end

    subgraph LaravelBackend["Laravel Backend"]
        C1[RecommendationController validasi user]
        C2[RecommendationService ambil route approved]
        C3[Bangun matriks alternatif]
        C4[TopsisService ranking TOPSIS]
        C5[DSSService anotasi risiko]
        C6[Kembalikan ranking]
    end

    subgraph Database
        D1[(users)]
        D2[(routes)]
        D3[(user_dss_preferences)]
    end

    subgraph ExternalService["External Service"]
        E1[OpenWeather API]
    end

    A1 --> D3
    A2 --> B1 --> C1
    C1 --> D1
    C1 --> C2 --> D2
    C2 --> C3 --> C4 --> C5
    C5 --> E1
    C5 --> C6 --> A3
```

## 4. Chatbot RAG

```mermaid
flowchart TD
    subgraph User
        A1[Kirim pesan chatbot]
        A2[Menerima jawaban]
    end

    subgraph ChatbotClient["Chatbot UI"]
        B1[POST /api/chat]
    end

    subgraph FlaskChatbot["Flask Chatbot"]
        C1[Route chat membaca role dan pesan]
        C2[Pilih context builder]
        C3[Retrieval data MySQL]
        C4[Context injection ke system prompt]
        C5[Panggil Gemini]
        C6[Clean markdown dan response]
    end

    subgraph Database
        D1[(MySQL aplikasi)]
        D2[(chat_histories)]
    end

    subgraph ExternalService["External Service"]
        E1[Gemini API]
    end

    A1 --> B1 --> C1 --> C2 --> C3 --> D1
    C3 --> C4 --> C5 --> E1 --> C6 --> A2
    C1 -. riwayat opsional .-> D2
```

## 5. Chatbot Booking

```mermaid
flowchart TD
    subgraph Pendaki
        A1[Meminta booking via chat]
        A2[Mengonfirmasi detail pesanan]
        A3[Menerima order dan arahan pembayaran]
    end

    subgraph FlaskChatbot
        B1[Bangun konteks pendaki]
        B2[Gemini meminta data booking]
        B3[Function call create_booking]
        B4[tools.py tool_create_booking]
    end

    subgraph LaravelBackend
        C1[POST /api/orders]
        C2[POST /api/transactions/store]
        C3[POST /api/midtrans/create-payment]
    end

    subgraph Database
        D1[(mountains/routes)]
        D2[(orders/order_members)]
        D3[(transactions)]
    end

    subgraph ExternalService
        E1[Gemini API]
        E2[Midtrans]
    end

    A1 --> B1 --> D1
    B1 --> B2 --> E1
    B2 --> A2
    A2 --> B3 --> B4 --> C1 --> D2
    C1 --> C2 --> D3
    C2 --> C3 --> E2
    C3 --> B4 --> A3
```

## 6. QR Check-in dan Check-out

```mermaid
flowchart TD
    subgraph Penjaga
        A1[Buka scanner]
        A2[Scan QR atau input manual]
        A3[Melihat hasil status]
    end

    subgraph WebPenjaga["Web Penjaga"]
        B1[GET /guards/scanner]
        B2[POST /guards/scanner/auto-scan/{id}]
    end

    subgraph LaravelBackend
        C1[TrailGuardController autoScan]
        C2[Validasi jalur milik penjaga]
        C3[Validasi transaksi Complete]
        C4{Status order}
        C5[Set Sedang Mendaki dan check_in]
        C6[Set Selesai dan check_out]
        C7[Tolak scan]
    end

    subgraph Database
        D1[(routes)]
        D2[(orders)]
        D3[(transactions)]
    end

    A1 --> B1
    A2 --> B2 --> C1 --> C2 --> D1
    C2 --> D2
    C2 --> C3 --> D3
    C3 --> C4
    C4 -- Booking --> C5 --> D2 --> A3
    C4 -- Sedang Mendaki --> C6 --> D2 --> A3
    C4 -- Expired/Cancelled/Selesai/lainnya --> C7 --> A3
```

## 7. Panic Button

```mermaid
flowchart TD
    subgraph Pendaki
        A1[Menekan panic button]
        A2[Mengirim lokasi dan tipe darurat]
        A3[Menerima status panic]
    end

    subgraph MobileApp
        B1[POST /api/panic]
        B2[GET /api/panic/order/{orderId}]
    end

    subgraph LaravelBackend
        C1[Middleware auth dan hiker.ready]
        C2[PanicController validasi order]
        C3[Cek status Sedang Mendaki]
        C4[Cek panic aktif]
        C5[Buat PanicRequest pending]
        C6[SarDashboardController respond/resolve]
    end

    subgraph Database
        D1[(orders)]
        D2[(panic_requests)]
    end

    subgraph Penjaga
        E1[Memantau SAR dashboard]
        E2[Merespons atau menyelesaikan]
    end

    A1 --> A2 --> B1 --> C1 --> C2 --> D1
    C2 --> C3 --> C4 --> D2
    C4 --> C5 --> D2 --> A3
    E1 --> C6 --> D2
    E2 --> C6
    A3 --> B2 --> D2
```

## 8. Offline Tracking dan Sync GPX

```mermaid
flowchart TD
    subgraph Pendaki
        A1[Mendaki dan merekam track offline]
        A2[Koneksi tersedia]
        A3[Menerima hasil sync]
    end

    subgraph MobileApp
        B1[Simpan cache GPX lokal]
        B2[POST /api/orders/{orderId}/offline-track-sync]
    end

    subgraph LaravelBackend
        C1[OrderController offlineTrackSync]
        C2[Validasi auth, order, dan kepemilikan]
        C3[Validasi status Sedang Mendaki]
        C4[Validasi ukuran GPX]
        C5[Cek duplikasi client_cache_id]
        C6[Simpan OfflineTrackSync]
    end

    subgraph Database
        D1[(orders)]
        D2[(offline_track_syncs)]
    end

    A1 --> B1 --> A2 --> B2 --> C1
    C1 --> C2 --> D1
    C2 --> C3 --> C4 --> C5 --> D2
    C5 -- Duplikat --> A3
    C5 -- Baru --> C6 --> D2 --> A3
```

## 9. Refund

```mermaid
flowchart TD
    subgraph Pendaki
        A1[Membuka refund preview]
        A2[Mengirim alasan dan metode refund]
        A3[Menerima status refund]
    end

    subgraph MobileApp
        B1[GET /api/refund-preview/{orderId}]
        B2[POST /api/refund-requests]
    end

    subgraph LaravelBackend
        C1[RefundRequestController preview]
        C2[Validasi order, transaksi, izin refund]
        C3[RefundCalculationService calculate]
        C4[RefundRequestController store]
        C5[Buat refund pending dan set Cancel Requested]
        C6[Admin approve/reject/refunded]
    end

    subgraph Database
        D1[(orders)]
        D2[(transactions)]
        D3[(routes)]
        D4[(refund_requests)]
    end

    subgraph Admin
        E1[Melihat refund request]
        E2[Approve, reject, atau tandai refunded]
    end

    A1 --> B1 --> C1 --> C2
    C2 --> D1
    C2 --> D2
    C2 --> D3
    C2 --> C3 --> A1
    A2 --> B2 --> C4 --> C2
    C4 --> C5 --> D4
    C5 --> D1
    E1 --> C6
    E2 --> C6 --> D4
    C6 --> D1
    A3 --> D4
```

