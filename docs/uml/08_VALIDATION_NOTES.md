# Validation Notes

## Fitur yang Terbukti dari Source Code

| Fitur | Bukti Source Code |
| --- | --- |
| Login reguler dan Google | `routes/api.php`, `AuthController@login`, `AuthController@loginWithGoogle`. |
| Booking tiket | `POST /api/orders`, `OrderController@createOrder`, model `Order`, `OrderMember`. |
| Validasi readiness pendaki | Middleware `EnsureHikerActionReadiness`, service `UserActionReadinessService`. |
| Validasi kuota harian | `OrderController::resolveDailyLimitViolation`, kolom `routes.daily_hiker_limit`. |
| DSS risk assessment saat booking | `DSSService::evaluateRoute`, dipanggil `OrderController@createOrder`. |
| Rekomendasi DSS TOPSIS | `RecommendationController@index`, `RecommendationService`, `TopsisService`. |
| Cuaca untuk DSS | `WeatherService`, `DSSService`, endpoint `GET /api/weather/current` dan `GET /api/weather/forecast`. |
| Pembayaran Midtrans | `MidtransController`, `MidtransService`, config `midtrans.php`. |
| Callback Midtrans | `POST /api/midtrans/notification`, `MidtransController@handleNotification`. |
| Status pembayaran polling | `GET /api/payment/status/{orderId}`, `GET /api/payment/qris-image/{orderId}`. |
| QR check-in/check-out | Web route `guards/scanner/auto-scan/{id}`, `TrailGuardController@autoScan`. |
| Panic button | `POST /api/panic`, `PanicController@store`, `PanicRequest`. |
| SAR dashboard | Web routes `guards/sar-dashboard`, `SarDashboardController`. |
| Offline track sync GPX | `POST /api/orders/{orderId}/offline-track-sync`, model/migration `OfflineTrackSync`. |
| Refund | `RefundRequestController`, `RefundCalculationService`, web/admin refund routes. |
| Chatbot RAG informasi | Flask `routes.py`, `gemini_engine.py`, `context_builders.py`, `database.py`. |
| Chatbot booking | `gemini_engine.py` function `create_booking`, `tools.py::tool_create_booking`, Laravel `/api/orders`, `/api/transactions/store`, `/api/midtrans/create-payment`. |
| Chatbot export Excel | `tools.py::tool_export_excel`, Flask endpoint `/api/chat/export/<filename>`. |
| Chat history | Laravel migration `create_chat_histories_table.php`, Flask routes `/api/chat/history`. |

## Fitur yang Perlu Verifikasi

| Area | Catatan |
| --- | --- |
| Flutter Mobile App | Source Flutter tidak ditemukan di workspace yang dianalisis. Nama screen seperti `BookingScreen`, `PaymentScreen`, dan `OfflineTrackingScreen` adalah boundary konseptual. |
| CRUD chatbot admin gunung | `tools.py` memanggil `POST/PUT/DELETE /api/mountains`, tetapi route API Laravel yang terbukti hanya `GET /api/mountains` dan `GET /api/mountains/home-feed`. Mutasi gunung tersedia pada web resource `/mountains`, bukan API JSON. |
| CRUD chatbot admin jalur | `tools.py` memanggil `/api/routes`, tetapi route API Laravel `/api/routes` tidak ditemukan. Alias `/api/trails` juga tidak ditemukan. Web resource yang ada adalah `/trails`. |
| Keamanan CRUD chatbot admin | Request `tool_crud_mountain` dan `tool_crud_trail` tidak terlihat mengirim Bearer token. Perlu verifikasi autentikasi/otorisasi sebelum dipakai produksi. |
| Kolom `check_in` dan `check_out` | `TrailGuardController` melakukan update kolom ini, tetapi migration eksplisit tidak ditemukan pada daftar file yang dianalisis. |
| Status transaksi manual lama | `TransactionController@updatePayment` memakai status `Unverified`, sedangkan migration awal enum `status_pesanan` hanya `Incomplete` dan `Complete`. Perlu verifikasi migration tambahan atau skema aktual. |
| `OrderMember` fillable | Model memakai `id_users`, sedangkan migration/relasi memakai `id_user`. Perlu verifikasi efeknya pada mass assignment. |
| `Trail::orders()` | Relasi pada model `Trail` menggunakan `hasOne(Order::class, 'user_id')`, yang tampak tidak sesuai domain jalur. Diagram memakai relasi berdasarkan migration `orders.id_jalur`. |
| Overdue monitoring | Ada sinkronisasi status expired pada `OrderController` dan `MidtransController`, tetapi scheduler/command khusus overdue monitoring belum terlihat selain lifecycle check saat endpoint dipanggil. |
| Force continue chatbot untuk high risk | Laravel mendukung `force_continue`; alur chatbot untuk mengirim ulang `force_continue` setelah warning belum terlihat eksplisit pada `tools.py`. |

## Endpoint yang Digunakan

### Laravel API

- `POST /api/register`
- `POST /api/login`
- `POST /api/auth/google`
- `GET /api/mountains`
- `GET /api/mountains/home-feed`
- `GET /api/mountains/{id_gunung}`
- `GET /api/mountains/{id_gunung}/trails/{id_jalur}`
- `GET /api/mountains/{id_gunung}/trails/{id_jalur}/preview`
- `GET /api/mountains/{id_gunung}/trails/{id_jalur}/booking`
- `GET /api/recommendations`
- `GET /api/dss-preferences`
- `POST /api/dss-preferences`
- `POST /api/orders`
- `GET /api/orders`
- `GET /api/orders/{orderId}`
- `POST /api/orders/{orderId}/offline-track-sync`
- `GET /api/orders/{orderId}/offline-track-syncs`
- `POST /api/transactions/store`
- `GET /api/transactions`
- `POST /api/midtrans/create-payment`
- `POST /api/midtrans/notification`
- `GET /api/midtrans/status/{orderId}`
- `GET /api/payment/status/{orderId}`
- `GET /api/payment/qris-image/{orderId}`
- `POST /api/panic`
- `GET /api/panic/order/{orderId}`
- `POST /api/panic/{id}/cancel`
- `GET /api/refund-preview/{orderId}`
- `POST /api/refund-requests`
- `GET /api/refund-requests/order/{orderId}`
- `GET /api/admin/refund-requests`
- `POST /api/admin/refund-requests/{id}/approve`
- `POST /api/admin/refund-requests/{id}/reject`
- `POST /api/admin/refund-requests/{id}/refunded`
- `GET /api/weather/current`
- `GET /api/weather/forecast`

### Laravel Web Penjaga/Admin

- `GET /guards/scanner`
- `POST /guards/scanner/auto-scan/{id}`
- `POST /guards/scanner/manual`
- `POST /guards/check-in/{id}`
- `POST /guards/check-out/{id}`
- `GET /guards/sar-dashboard`
- `POST /guards/sar-dashboard/{id}/respond`
- `POST /guards/sar-dashboard/{id}/resolve`
- `GET /admin/refunds`
- `POST /admin/refunds/{id}/approve`
- `POST /admin/refunds/{id}/reject`
- `POST /admin/refunds/{id}/refunded`
- Web resource `/mountains`, `/trails`, `/rules`, `/users`.

### Flask Chatbot

- `POST /api/chat`
- `GET /api/chat/info`
- `GET /api/chat/export/<filename>`
- `GET /api/chat/history?user_id=&role=`
- `GET /api/chat/history/<id>`
- `POST /api/chat/history`
- `DELETE /api/chat/history/<id>`
- `GET /api/health`

## File Penting Dasar Diagram

| Area | File |
| --- | --- |
| API routes | `routes/api.php` |
| Web routes | `routes/web.php` |
| Booking dan offline sync | `app/Http/Controllers/Api/OrderController.php` |
| Pembayaran | `app/Http/Controllers/Api/MidtransController.php`, `app/Services/MidtransService.php` |
| Transaksi | `app/Http/Controllers/Api/TransactionController.php`, `app/Models/Transaction.php` |
| DSS TOPSIS | `app/Http/Controllers/RecommendationController.php`, `app/Services/RecommendationService.php`, `app/Services/TopsisService.php` |
| Risk assessment | `app/Services/DSSService.php`, `app/Services/WeatherService.php` |
| Panic/SAR | `app/Http/Controllers/Api/PanicController.php`, `app/Http/Controllers/SarDashboardController.php` |
| QR check-in/out | `app/Http/Controllers/TrailGuardController.php` |
| Refund | `app/Http/Controllers/Api/RefundRequestController.php`, `app/Services/RefundCalculationService.php` |
| Readiness middleware | `app/Http/Middleware/EnsureHikerActionReadiness.php` |
| Model utama | `app/Models/User.php`, `Mountain.php`, `Trail.php`, `Order.php`, `Transaction.php`, `PanicRequest.php`, `RefundRequest.php`, `OfflineTrackSync.php` |
| Migration utama | `database/migrations/*create_*`, `*add_dss*`, `*refund*`, `*offline_track_syncs*`, `*user_dss_preferences*` |
| Flask routes | `My_Hiking_Chatbot/routes.py` |
| Gemini function calling | `My_Hiking_Chatbot/gemini_engine.py` |
| Flask tools | `My_Hiking_Chatbot/tools.py` |
| RAG retrieval | `My_Hiking_Chatbot/database.py`, `My_Hiking_Chatbot/context_builders.py` |

## Catatan Batasan

1. Diagram menggunakan Bahasa Indonesia akademik dan memisahkan boundary, control, dan entity pada sequence diagram.
2. Chatbot RAG tidak memakai vector database pada kode yang ditemukan. Retrieval dilakukan lewat query MySQL terstruktur, sehingga istilah yang tepat adalah database-grounded RAG atau structured-data RAG.
3. DSS mempunyai dua peran berbeda: TOPSIS untuk ranking rekomendasi jalur, dan rule-based risk assessment untuk validasi risiko jalur saat booking serta anotasi risiko rekomendasi.
4. Endpoint CRUD chatbot admin perlu diselaraskan dengan Laravel API sebelum dinyatakan final.
5. Beberapa flow mobile tidak bisa diverifikasi sampai source Flutter tersedia.

