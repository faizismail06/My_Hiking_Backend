# Use Case Diagram

## Aktor Sistem

- **Pendaki**: pengguna mobile app yang melakukan pencarian jalur, booking, pembayaran, chatbot, panic button, tracking, dan refund.
- **Penjaga Basecamp / Penjaga Jalur**: pengguna web penjaga yang mengelola jalur, melakukan scan QR, check-in/check-out, dan memantau panic request.
- **Admin**: pengguna web admin yang mengelola data master, transaksi, refund, withdrawal, dan laporan.
- **Layanan Eksternal**: Midtrans, Gemini API, Google OAuth, dan OpenWeather API.

## Use Case Utama

- Pendaki: registrasi/login, lihat gunung dan jalur, rekomendasi DSS, booking tiket, pembayaran, chatbot, panic button, offline tracking, dan refund.
- Penjaga: login, kelola jalur, scan QR, check-in/check-out, pantau SAR, laporan pendapatan, dan withdrawal.
- Admin: login, kelola master data, kelola user, proses refund, kelola withdrawal, dan chatbot admin.
- Layanan eksternal: autentikasi Google, pembayaran Midtrans, respons Gemini, dan data cuaca OpenWeather.

## Diagram Use Case Sederhana

Mermaid tidak menyediakan notasi use case UML murni. Diagram berikut memakai `flowchart` dengan node berbentuk oval/stadium agar mendekati bentuk use case diagram konvensional.

```mermaid
flowchart LR
    Pendaki[Pendaki]
    Penjaga[Penjaga Basecamp / Penjaga Jalur]
    Admin[Admin]
    Midtrans[Midtrans]
    Gemini[Gemini API]
    Google[Google OAuth]
    Weather[OpenWeather API]

    subgraph Sistem["Sistem Pendakian Terintegrasi My Hiking"]
        UCRegister([Registrasi])
        UCLogin([Login])
        UCGoogle([Login Google])
        UCBrowse([Lihat Gunung dan Jalur])
        UCDSS([Rekomendasi DSS])
        UCBooking([Booking Tiket])
        UCPayment([Pembayaran])
        UCChatInfo([Chatbot RAG Informasi])
        UCChatBooking([Chatbot Booking])
        UCPanic([Panic Button])
        UCTracking([Offline Tracking dan Sync GPX])
        UCRefund([Refund])

        UCKelolaMaster([Kelola Master Data])
        UCKelolaUser([Kelola User])
        UCProsesRefund([Proses Refund])
        UCWithdrawalAdmin([Kelola Withdrawal])
        UCChatAdmin([Chatbot CRUD Admin])

        UCKelolaJalur([Kelola Jalur])
        UCScanQR([Scan QR Tiket])
        UCCheckInOut([Check-in dan Check-out])
        UCSAR([Pantau SAR / Panic Request])
        UCLaporan([Laporan Pendapatan])
        UCWithdrawalGuard([Ajukan Withdrawal])

        UCValidasiData([Validasi Data])
        UCDSSRisk([Validasi Risiko DSS])
        UCMidtransStatus([Cek Status Pembayaran])
        UCGeminiContext([Retrieval Data dan Context Injection])
        UCCuaca([Ambil Data Cuaca])
    end

    Pendaki --> UCRegister
    Pendaki --> UCLogin
    Pendaki --> UCGoogle
    Pendaki --> UCBrowse
    Pendaki --> UCDSS
    Pendaki --> UCBooking
    Pendaki --> UCPayment
    Pendaki --> UCChatInfo
    Pendaki --> UCChatBooking
    Pendaki --> UCPanic
    Pendaki --> UCTracking
    Pendaki --> UCRefund

    Admin --> UCLogin
    Admin --> UCKelolaMaster
    Admin --> UCKelolaUser
    Admin --> UCProsesRefund
    Admin --> UCWithdrawalAdmin
    Admin --> UCChatAdmin

    Penjaga --> UCLogin
    Penjaga --> UCKelolaJalur
    Penjaga --> UCScanQR
    Penjaga --> UCCheckInOut
    Penjaga --> UCSAR
    Penjaga --> UCLaporan
    Penjaga --> UCWithdrawalGuard

    UCRegister -. "<<include>>" .-> UCValidasiData
    UCLogin -. "<<include>>" .-> UCValidasiData
    UCGoogle -. "<<include>>" .-> Google
    UCBooking -. "<<include>>" .-> UCDSSRisk
    UCBooking -. "<<include>>" .-> UCValidasiData
    UCDSS -. "<<include>>" .-> UCCuaca
    UCDSSRisk -. "<<include>>" .-> UCCuaca
    UCPayment -. "<<include>>" .-> UCMidtransStatus
    UCCheckInOut -. "<<extend>>" .-> UCScanQR
    UCChatBooking -. "<<include>>" .-> UCBooking
    UCChatInfo -. "<<include>>" .-> UCGeminiContext
    UCChatAdmin -. "<<include>>" .-> UCGeminiContext

    UCPayment --> Midtrans
    UCMidtransStatus --> Midtrans
    UCChatInfo --> Gemini
    UCChatBooking --> Gemini
    UCChatAdmin --> Gemini
    UCCuaca --> Weather
```

## Catatan

- Diagram dibuat ringkas agar mudah ditempatkan pada BAB III.
- Detail skenario setiap fitur tetap dijelaskan pada `02_USE_CASE_SCENARIOS.md`.
- CRUD chatbot admin untuk mutasi `/api/mountains`, `/api/routes`, dan alias `/api/trails` tetap **perlu verifikasi** sesuai catatan validasi karena endpoint API mutasi tidak ditemukan pada `routes/api.php`.

