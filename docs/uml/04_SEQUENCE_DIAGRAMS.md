# Sequence Diagrams

## 1. Login

```mermaid
sequenceDiagram
    actor User
    participant LoginScreen as "Boundary: LoginScreen"
    participant ApiService as "Boundary: ApiService"
    participant AuthController as "Control: AuthController"
    participant GoogleOAuth as "External: Google OAuth"
    participant UserEntity as "Entity: User"
    participant DB as Database

    alt Login email/password
        User->>LoginScreen: Input email dan password
        LoginScreen->>ApiService: POST /api/login
        ApiService->>AuthController: login(request)
        AuthController->>UserEntity: cari user dan validasi password
        UserEntity->>DB: SELECT users
        DB-->>UserEntity: data user
        AuthController-->>ApiService: token Sanctum dan data user
        ApiService-->>LoginScreen: response sukses
    else Login Google
        User->>LoginScreen: Pilih login Google
        LoginScreen->>GoogleOAuth: autentikasi Google
        GoogleOAuth-->>LoginScreen: token/profil Google
        LoginScreen->>ApiService: POST /api/auth/google
        ApiService->>AuthController: loginWithGoogle(request)
        AuthController->>UserEntity: create/update user
        UserEntity->>DB: UPSERT users
        AuthController-->>ApiService: token Sanctum dan data user
        ApiService-->>LoginScreen: response sukses
    end
```

## 2. Booking Tiket + DSS

```mermaid
sequenceDiagram
    actor Pendaki
    participant BookingScreen as "Boundary: BookingScreen"
    participant ApiService as "Boundary: ApiService"
    participant ReadyMiddleware as "Control: EnsureHikerActionReadiness"
    participant OrderController as "Control: OrderController"
    participant DSSService as "Control: DSSService"
    participant WeatherService as "Control: WeatherService"
    participant OpenWeather as "External: OpenWeather API"
    participant Order as "Entity: Order"
    participant OrderMember as "Entity: OrderMember"
    participant Trail as "Entity: Route"
    participant DB as Database

    Pendaki->>BookingScreen: Submit booking
    BookingScreen->>ApiService: POST /api/orders
    ApiService->>ReadyMiddleware: auth:sanctum + hiker.ready
    ReadyMiddleware->>DB: validasi users dan user_experiences
    ReadyMiddleware->>OrderController: lanjutkan request
    OrderController->>Trail: validasi id_jalur dan id_gunung
    Trail->>DB: SELECT routes
    OrderController->>Order: hitung okupansi tanggal
    Order->>DB: SELECT orders with members_count
    alt Pendaki level 1
        OrderController->>DSSService: evaluateRoute(user, trail)
        DSSService->>WeatherService: getCurrentWeather(lat, lng)
        WeatherService->>OpenWeather: request cuaca
        OpenWeather-->>WeatherService: data cuaca
        WeatherService-->>DSSService: weather_score
        DSSService-->>OrderController: risk_level dan warning
    end
    alt Risiko tinggi tanpa force_continue
        OrderController-->>ApiService: 409 HIGH_RISK_CONFIRMATION_REQUIRED
        ApiService-->>BookingScreen: tampilkan konfirmasi
    else Valid
        OrderController->>Order: create
        Order->>DB: INSERT orders
        OrderController->>OrderMember: syncWithoutDetaching anggota
        OrderMember->>DB: INSERT order_members
        OrderController-->>ApiService: 201 order dan dss
        ApiService-->>BookingScreen: tampilkan order
    end
```

## 3. Pembayaran Midtrans

```mermaid
sequenceDiagram
    actor Pendaki
    participant PaymentScreen as "Boundary: PaymentScreen"
    participant ApiService as "Boundary: ApiService"
    participant MidtransController as "Control: MidtransController"
    participant MidtransService as "Control: MidtransService"
    participant Midtrans as "External: Midtrans Gateway"
    participant Transaction as "Entity: Transaction"
    participant Order as "Entity: Order"
    participant DB as Database

    Pendaki->>PaymentScreen: Pilih metode pembayaran
    PaymentScreen->>ApiService: POST /api/midtrans/create-payment
    ApiService->>MidtransController: createPayment(request)
    MidtransController->>Order: load order, mountain, trail, booker, members
    Order->>DB: SELECT orders
    MidtransController->>Transaction: find existing transaction
    Transaction->>DB: SELECT transactions
    MidtransController->>MidtransService: buildDirectChargeParams atau buildTransactionParams
    alt Direct charge tersedia
        MidtransService->>Midtrans: charge
        Midtrans-->>MidtransService: instruction/payment code/QR
    else Snap fallback
        MidtransService->>Midtrans: createSnapToken
        Midtrans-->>MidtransService: snap_token dan redirect_url
    end
    MidtransController->>Transaction: create atau update
    Transaction->>DB: INSERT/UPDATE transactions
    MidtransController-->>ApiService: instruksi pembayaran
    ApiService-->>PaymentScreen: tampilkan pembayaran

    Midtrans->>MidtransController: POST /api/midtrans/notification
    MidtransController->>MidtransService: verifySignature
    MidtransService-->>MidtransController: valid
    MidtransController->>Transaction: update payment_status/status_pesanan
    Transaction->>DB: UPDATE transactions
    MidtransController->>Order: update status Booking atau Expired
    Order->>DB: UPDATE orders
```

## 4. Chatbot RAG Informasi

```mermaid
sequenceDiagram
    actor User
    participant ChatbotUI as "Boundary: ChatbotUI"
    participant FlaskRoute as "Boundary: Flask /api/chat"
    participant GeminiEngine as "Control: gemini_engine.py"
    participant ContextBuilder as "Control: context_builders.py"
    participant DatabasePy as "Control: database.py"
    participant Gemini as "External: Gemini API"
    participant DB as "Entity: MySQL Database"

    User->>ChatbotUI: Kirim pertanyaan
    ChatbotUI->>FlaskRoute: POST /api/chat
    FlaskRoute->>GeminiEngine: get_gemini_response(message, role)
    GeminiEngine->>ContextBuilder: build_context_by_role
    ContextBuilder->>DatabasePy: fetch_mountains/trails/rules/orders
    DatabasePy->>DB: SELECT data relevan
    DB-->>DatabasePy: hasil retrieval
    DatabasePy-->>ContextBuilder: structured data
    ContextBuilder-->>GeminiEngine: konteks teks
    GeminiEngine->>Gemini: prompt + konteks + tools role
    Gemini-->>GeminiEngine: jawaban
    GeminiEngine-->>FlaskRoute: response bersih
    FlaskRoute-->>ChatbotUI: JSON response
    ChatbotUI-->>User: Tampilkan jawaban
```

## 5. Chatbot Booking

```mermaid
sequenceDiagram
    actor Pendaki
    participant ChatbotUI as "Boundary: ChatbotUI"
    participant FlaskRoute as "Boundary: Flask /api/chat"
    participant GeminiEngine as "Control: gemini_engine.py"
    participant Tools as "Control: tools.py"
    participant LaravelOrders as "Boundary: POST /api/orders"
    participant LaravelTx as "Boundary: POST /api/transactions/store"
    participant LaravelPay as "Boundary: POST /api/midtrans/create-payment"
    participant OrderController as "Control: OrderController"
    participant TransactionController as "Control: TransactionController"
    participant MidtransController as "Control: MidtransController"
    participant Order as "Entity: Order"
    participant Transaction as "Entity: Transaction"
    participant DB as Database
    participant Midtrans as "External: Midtrans"

    Pendaki->>ChatbotUI: Minta booking
    ChatbotUI->>FlaskRoute: POST /api/chat
    FlaskRoute->>GeminiEngine: get_gemini_response
    GeminiEngine-->>ChatbotUI: Tanya data dan konfirmasi
    Pendaki->>ChatbotUI: Konfirmasi detail
    ChatbotUI->>FlaskRoute: POST /api/chat
    FlaskRoute->>GeminiEngine: proses pesan konfirmasi
    GeminiEngine->>Tools: function call create_booking
    Tools->>LaravelOrders: POST /api/orders dengan Bearer token
    LaravelOrders->>OrderController: createOrder
    OrderController->>Order: create
    Order->>DB: INSERT orders/order_members
    OrderController-->>Tools: order_id
    Tools->>LaravelTx: POST /api/transactions/store
    LaravelTx->>TransactionController: store
    TransactionController->>Transaction: create
    Transaction->>DB: INSERT transactions
    TransactionController-->>Tools: transaction_id
    Tools->>LaravelPay: POST /api/midtrans/create-payment
    LaravelPay->>MidtransController: createPayment
    MidtransController->>Midtrans: create payment
    Midtrans-->>MidtransController: redirect/instruction
    MidtransController-->>Tools: payment data
    Tools-->>GeminiEngine: function result
    GeminiEngine-->>FlaskRoute: final response
    FlaskRoute-->>ChatbotUI: order_id, transaction_id, payment_url
```

## 6. Chatbot CRUD Admin

```mermaid
sequenceDiagram
    actor Admin
    participant AdminChatUI as "Boundary: AdminChatUI"
    participant FlaskRoute as "Boundary: Flask /api/chat"
    participant GeminiEngine as "Control: gemini_engine.py"
    participant Tools as "Control: tools.py"
    participant LaravelApi as "Boundary: Laravel API"
    participant DBPy as "Control: database.py"
    participant DB as "Entity: MySQL Database"

    Admin->>AdminChatUI: Minta list/CRUD gunung atau jalur
    AdminChatUI->>FlaskRoute: POST /api/chat role admin
    FlaskRoute->>GeminiEngine: get_gemini_response
    alt List data
        GeminiEngine->>Tools: crud_mountain/crud_trail action list
        Tools->>DBPy: fetch_mountains_data atau fetch_trails_data
        DBPy->>DB: SELECT mountains/routes
        DB-->>DBPy: data
        DBPy-->>Tools: hasil list
    else Create/update/delete
        GeminiEngine->>Tools: crud_mountain/crud_trail action mutasi
        Tools->>LaravelApi: POST/PUT/DELETE /api/mountains atau /api/routes
        LaravelApi-->>Tools: perlu verifikasi endpoint
    end
    Tools-->>GeminiEngine: hasil function
    GeminiEngine-->>FlaskRoute: final response
    FlaskRoute-->>AdminChatUI: tampilkan hasil
```

Catatan: endpoint mutasi `/api/mountains`, `/api/routes`, dan alias `/api/trails` tidak ditemukan pada `routes/api.php`; bagian ini perlu verifikasi.

## 7. QR Check-in dan Check-out

```mermaid
sequenceDiagram
    actor Penjaga
    participant ScannerPage as "Boundary: guards/scanner"
    participant AutoScanEndpoint as "Boundary: POST /guards/scanner/auto-scan/{id}"
    participant TrailGuardController as "Control: TrailGuardController"
    participant Trail as "Entity: Route"
    participant Order as "Entity: Order"
    participant Transaction as "Entity: Transaction"
    participant DB as Database

    Penjaga->>ScannerPage: Scan QR order
    ScannerPage->>AutoScanEndpoint: POST order id
    AutoScanEndpoint->>TrailGuardController: autoScan(orderId)
    TrailGuardController->>Trail: cari jalur milik penjaga
    Trail->>DB: SELECT routes WHERE user_id
    TrailGuardController->>Order: cari order pada jalur
    Order->>DB: SELECT orders
    TrailGuardController->>Transaction: validasi Complete
    Transaction->>DB: SELECT transactions
    alt Status Booking
        TrailGuardController->>Order: update status Sedang Mendaki dan check_in
        Order->>DB: UPDATE orders
        TrailGuardController-->>ScannerPage: check-in sukses
    else Status Sedang Mendaki
        TrailGuardController->>Order: update status Selesai dan check_out
        Order->>DB: UPDATE orders
        TrailGuardController-->>ScannerPage: check-out sukses
    else Status tidak valid
        TrailGuardController-->>ScannerPage: error
    end
```

## 8. Panic Button

```mermaid
sequenceDiagram
    actor Pendaki
    participant PanicScreen as "Boundary: PanicScreen"
    participant ApiService as "Boundary: ApiService"
    participant ReadyMiddleware as "Control: EnsureHikerActionReadiness"
    participant PanicController as "Control: PanicController"
    participant SarDashboard as "Boundary: SAR Dashboard"
    participant SarController as "Control: SarDashboardController"
    participant PanicRequest as "Entity: PanicRequest"
    participant Order as "Entity: Order"
    participant DB as Database
    actor Penjaga

    Pendaki->>PanicScreen: Tekan panic button
    PanicScreen->>ApiService: POST /api/panic
    ApiService->>ReadyMiddleware: auth:sanctum + hiker.ready
    ReadyMiddleware->>PanicController: request valid
    PanicController->>Order: cek order milik user dan status Sedang Mendaki
    Order->>DB: SELECT orders
    PanicController->>PanicRequest: cek panic aktif
    PanicRequest->>DB: SELECT panic_requests
    PanicController->>PanicRequest: create pending
    PanicRequest->>DB: INSERT panic_requests
    PanicController-->>ApiService: panic created
    ApiService-->>PanicScreen: status pending

    Penjaga->>SarDashboard: Buka dashboard SAR
    SarDashboard->>SarController: GET /guards/sar-dashboard
    SarController->>PanicRequest: daftar panic jalur penjaga
    PanicRequest->>DB: SELECT panic_requests
    Penjaga->>SarDashboard: Respond/Resolve
    SarDashboard->>SarController: POST respond atau resolve
    SarController->>PanicRequest: update status
    PanicRequest->>DB: UPDATE panic_requests
```

## 9. Offline Sync GPX

```mermaid
sequenceDiagram
    actor Pendaki
    participant TrackingScreen as "Boundary: OfflineTrackingScreen"
    participant LocalCache as "Entity: Local GPX Cache"
    participant ApiService as "Boundary: ApiService"
    participant OrderController as "Control: OrderController"
    participant Order as "Entity: Order"
    participant OfflineTrackSync as "Entity: OfflineTrackSync"
    participant DB as Database

    Pendaki->>TrackingScreen: Rekam pendakian offline
    TrackingScreen->>LocalCache: Simpan GPX dan metadata
    TrackingScreen->>ApiService: POST /api/orders/{orderId}/offline-track-sync
    ApiService->>OrderController: offlineTrackSync(request)
    OrderController->>Order: validasi order dan kepemilikan
    Order->>DB: SELECT orders
    alt Status bukan Sedang Mendaki
        OrderController-->>ApiService: 409 ORDER_STATUS_NOT_SYNCABLE
        ApiService-->>TrackingScreen: gagal sync
    else Valid
        OrderController->>OfflineTrackSync: cek client_cache_id
        OfflineTrackSync->>DB: SELECT offline_track_syncs
        alt Duplikat
            OrderController-->>ApiService: success duplicate
        else Baru
            OrderController->>OfflineTrackSync: create synced
            OfflineTrackSync->>DB: INSERT offline_track_syncs
            OrderController-->>ApiService: 201 synced
        end
        ApiService-->>TrackingScreen: tampilkan hasil sync
    end
```
