# Use Case Scenarios

## 1. Booking Tiket

**Nama Use Case:** Booking Tiket  
**Aktor Utama:** Pendaki  
**Tujuan:** Pendaki membuat pesanan tiket untuk gunung dan jalur tertentu.  
**Prasyarat:** Pendaki sudah login, profil lengkap, pengalaman/tier tersedia, memilih gunung, jalur, tanggal naik, tanggal turun, dan anggota jika ada.

**Alur Utama:**
1. Pendaki membuka detail jalur dan memilih menu booking.
2. Mobile App mengirim data booking ke `POST /api/orders`.
3. Middleware `hiker.ready` memvalidasi kepemilikan akun, kelengkapan profil, dan onboarding pengalaman.
4. `OrderController` memvalidasi gunung, jalur, tanggal, harga tiket, dan anggota.
5. Sistem memeriksa kuota harian jalur berdasarkan `daily_hiker_limit`.
6. Jika pendaki level 1, `DSSService` mengevaluasi risiko jalur berdasarkan tier, metrik jalur, dan cuaca.
7. Jika risiko aman atau sudah dikonfirmasi untuk lanjut, sistem membuat `Order` dan relasi `OrderMember`.
8. Sistem mengembalikan data pesanan beserta hasil DSS/warning jika ada.

**Alur Alternatif:**
- Jika profil belum lengkap, sistem mengembalikan `PROFILE_INCOMPLETE`.
- Jika onboarding pengalaman belum selesai, sistem mengembalikan `EXPERIENCE_ONBOARDING_REQUIRED`.
- Jika kuota jalur melebihi batas, sistem mengembalikan `TRAIL_DAILY_LIMIT_EXCEEDED`.
- Jika risiko tinggi dan `force_continue` belum dikirim, sistem mengembalikan `HIGH_RISK_CONFIRMATION_REQUIRED`.

**Kondisi Akhir:** Order tersimpan dengan status awal `Booking`, atau request ditolak dengan alasan validasi.

## 2. Pembayaran Midtrans

**Nama Use Case:** Pembayaran Midtrans  
**Aktor Utama:** Pendaki  
**Tujuan:** Pendaki membayar pesanan melalui Midtrans.  
**Prasyarat:** Order sudah dibuat dan belum lunas, belum expired, dan tidak dalam status refund/cancel.

**Alur Utama:**
1. Pendaki menekan tombol pembayaran.
2. Mobile App memanggil `POST /api/midtrans/create-payment`.
3. `MidtransController` mengambil order, user, anggota, dan menghitung total pembayaran.
4. Sistem membuat atau memperbarui `Transaction`.
5. Jika metode pembayaran dipilih dan didukung, `MidtransService` membuat direct charge.
6. Jika direct charge tidak digunakan, sistem membuat Snap token Midtrans.
7. Mobile App menampilkan instruksi pembayaran, QRIS, deeplink, atau redirect Snap.
8. Midtrans mengirim callback ke `POST /api/midtrans/notification`.
9. Sistem memverifikasi signature, memperbarui `Transaction`, dan mengubah `Order` menjadi `Booking` jika pembayaran lunas.

**Alur Alternatif:**
- Jika transaksi pending masih valid, sistem dapat memakai transaksi sebelumnya dengan `reuse_if_pending`.
- Jika pembayaran expired, status transaksi menjadi `expired` dan order menjadi `Expired`.
- Jika signature Midtrans tidak valid, callback ditolak.

**Kondisi Akhir:** Transaksi berstatus `paid/Complete`, `pending`, `expired`, atau `failed`; order tetap `Booking` jika pembayaran sukses.

## 3. Rekomendasi DSS

**Nama Use Case:** Rekomendasi DSS  
**Aktor Utama:** Pendaki  
**Tujuan:** Pendaki memperoleh ranking jalur berdasarkan preferensi.  
**Prasyarat:** Pendaki login sebagai level 1 dan memiliki tier pengalaman.

**Alur Utama:**
1. Pendaki membuka fitur rekomendasi dan mengatur bobot preferensi.
2. Mobile App memanggil `GET /api/recommendations` dengan bobot prioritas.
3. `RecommendationController` memvalidasi user, level, dan tier.
4. `RecommendationService` mengambil route dengan `dss_status = approved`.
5. Sistem membangun alternatif berdasarkan jarak, elevasi, durasi, biaya, difficulty, crowd, panorama, fasilitas, popularitas, dan safety.
6. `TopsisService` melakukan normalisasi min-max, pembobotan, solusi ideal, jarak ideal, dan closeness coefficient.
7. `DSSService` memberi anotasi risiko rule-based untuk tiap route.
8. Sistem mengembalikan ranking, skor, faktor kunci, dan warning.

**Alur Alternatif:**
- Jika user belum login, sistem mengembalikan `UNAUTHORIZED`.
- Jika bukan pendaki, sistem mengembalikan `HIKER_ONLY`.
- Jika tier belum ada, sistem mengembalikan `EXPERIENCE_ONBOARDING_REQUIRED`.

**Kondisi Akhir:** Pendaki menerima daftar rekomendasi jalur terurut berdasarkan TOPSIS.

## 4. Chatbot RAG Informasi

**Nama Use Case:** Chatbot RAG Informasi  
**Aktor Utama:** Pendaki/Admin/Penjaga  
**Tujuan:** User mendapatkan jawaban berbasis data sistem.  
**Prasyarat:** Flask Chatbot Service aktif dan dapat mengakses MySQL serta Gemini API.

**Alur Utama:**
1. User mengirim pesan ke `POST /api/chat`.
2. Flask route membaca role, user id, riwayat chat, token, dan pesan.
3. `gemini_engine.py` memilih context builder sesuai role.
4. `context_builders.py` mengambil data MySQL melalui `database.py`.
5. Sistem menyisipkan konteks database ke system prompt Gemini.
6. Gemini menghasilkan jawaban berbasis konteks.
7. Flask mengembalikan jawaban ke client.

**Alur Alternatif:**
- Jika role tidak valid, role diubah menjadi `pendaki`.
- Jika Gemini/API error, sistem mengembalikan pesan gagal umum.
- Jika data tidak ada dalam konteks, prompt menginstruksikan chatbot untuk tidak mengarang.

**Kondisi Akhir:** User menerima jawaban RAG berbasis data MySQL.

## 5. Chatbot Booking

**Nama Use Case:** Chatbot Booking  
**Aktor Utama:** Pendaki  
**Tujuan:** Pendaki membuat booking melalui percakapan chatbot.  
**Prasyarat:** Pendaki login, token auth dikirim ke Flask, dan data gunung/jalur tersedia di MySQL.

**Alur Utama:**
1. Pendaki meminta booking melalui chatbot.
2. Chatbot menanyakan gunung, jalur, tanggal, tipe pendakian, dan anggota.
3. Chatbot menampilkan ringkasan pesanan dan meminta konfirmasi.
4. Setelah user setuju, Gemini memanggil function `create_booking`.
5. `gemini_engine.py` memproses function call dan memanggil `tool_create_booking` di `tools.py`.
6. `tools.py` memanggil `POST /api/orders` pada Laravel.
7. Jika order berhasil, `tools.py` memanggil `POST /api/transactions/store`.
8. Jika transaksi berhasil, `tools.py` memanggil `POST /api/midtrans/create-payment`.
9. Chatbot mengembalikan ringkasan booking dan link/status pembayaran.

**Alur Alternatif:**
- Jika token tidak ada, tools mengembalikan `UNAUTHORIZED`.
- Jika profil atau onboarding belum lengkap, tools menormalisasi error Laravel.
- Jika Midtrans gagal, booking tetap dapat berhasil dan user diarahkan ke menu transaksi.
- Jika risiko tinggi, Laravel dapat mengembalikan `HIGH_RISK_CONFIRMATION_REQUIRED`; mekanisme konfirmasi lanjutan perlu verifikasi.

**Kondisi Akhir:** Order dibuat melalui Laravel API, dan pembayaran disiapkan jika tidak gagal.

## 6. Chatbot CRUD Admin

**Nama Use Case:** Chatbot CRUD Admin  
**Aktor Utama:** Admin  
**Tujuan:** Admin mengelola data gunung/jalur melalui chatbot.  
**Prasyarat:** Admin mengakses chatbot dengan role `admin`.

**Alur Utama:**
1. Admin meminta daftar, tambah, ubah, atau hapus data gunung/jalur.
2. Chatbot mengumpulkan data yang diperlukan.
3. Untuk create/update/delete, chatbot meminta konfirmasi.
4. Gemini memanggil function `crud_mountain` atau `crud_trail`.
5. `tools.py` menjalankan fetch data atau request HTTP ke Laravel API.
6. Chatbot menampilkan hasil operasi.

**Alur Alternatif:**
- `list` untuk gunung/jalur terbukti mengambil langsung dari MySQL.
- `create/update/delete` gunung di `tools.py` memanggil `/api/mountains`, tetapi route API Laravel untuk metode tersebut tidak ditemukan; perlu verifikasi.
- `create/update/delete` jalur memanggil `/api/routes`, tetapi route API Laravel dan alias `/api/trails` tidak ditemukan; perlu verifikasi.
- Autentikasi/otorisasi tools CRUD ke Laravel tidak terlihat pada header request; perlu verifikasi keamanan.

**Kondisi Akhir:** Operasi list dapat berjalan dari database; operasi mutasi data perlu verifikasi endpoint.

## 7. QR Check-in dan Check-out

**Nama Use Case:** QR Check-in dan Check-out  
**Aktor Utama:** Penjaga Basecamp / Penjaga Jalur  
**Tujuan:** Penjaga mengubah status order saat pendaki masuk/keluar jalur.  
**Prasyarat:** Penjaga login web, memiliki jalur, order sesuai jalur, dan transaksi sudah `Complete`.

**Alur Utama:**
1. Penjaga membuka scanner QR.
2. Web penjaga membaca ID pesanan dari QR atau pencarian manual.
3. Sistem memvalidasi order berada pada jalur penjaga.
4. Sistem memvalidasi transaksi sudah `Complete`.
5. Pada scan pertama, `autoScan` mengubah status order dari `Booking` menjadi `Sedang Mendaki` dan mengisi `check_in`.
6. Pada scan berikutnya, `autoScan` mengubah status dari `Sedang Mendaki` menjadi `Selesai` dan mengisi `check_out`.

**Alur Alternatif:**
- Jika order bukan milik jalur penjaga, sistem menolak.
- Jika pembayaran belum `Complete`, sistem menolak.
- Jika order `Expired` atau `Cancelled`, sistem menolak.
- Kolom `check_in` dan `check_out` digunakan di controller, tetapi migration eksplisitnya tidak ditemukan dalam daftar awal; perlu verifikasi skema aktual.

**Kondisi Akhir:** Status order berubah sesuai fase pendakian.

## 8. Panic Button

**Nama Use Case:** Panic Button  
**Aktor Utama:** Pendaki  
**Tujuan:** Pendaki mengirim permintaan darurat saat sedang mendaki.  
**Prasyarat:** Pendaki login, memiliki order aktif berstatus `Sedang Mendaki`, dan mengirim koordinat.

**Alur Utama:**
1. Pendaki menekan panic button di mobile app.
2. Mobile App mengirim `POST /api/panic`.
3. Middleware memvalidasi kesiapan aksi pendaki.
4. `PanicController` memvalidasi order milik user dan status `Sedang Mendaki`.
5. Sistem mengecek tidak ada panic aktif untuk order tersebut.
6. Sistem membuat `PanicRequest` berstatus `pending`.
7. Penjaga melihat panic pada SAR dashboard.
8. Penjaga merespons panic sehingga status menjadi `responding`.
9. Penjaga menyelesaikan panic sehingga status menjadi `resolved`.

**Alur Alternatif:**
- Jika order tidak ditemukan/bukan milik user, sistem menolak.
- Jika order belum `Sedang Mendaki`, sistem menolak.
- Jika panic aktif sudah ada, sistem menolak.
- Pendaki dapat membatalkan panic jika masih `pending`.

**Kondisi Akhir:** Panic request tercatat dan diproses oleh penjaga/SAR.

## 9. Offline Tracking dan Sync GPX

**Nama Use Case:** Offline Tracking dan Sync GPX  
**Aktor Utama:** Pendaki  
**Tujuan:** Pendaki menyinkronkan track offline saat kembali online.  
**Prasyarat:** Pendaki login, order milik user, status order `Sedang Mendaki`, dan data GPX tersedia.

**Alur Utama:**
1. Mobile App menyimpan track lokal ketika offline.
2. Saat jaringan tersedia, Mobile App mengirim `POST /api/orders/{orderId}/offline-track-sync`.
3. `OrderController` memvalidasi user, order, status order, metadata, dan ukuran GPX.
4. Sistem mengecek duplikasi berdasarkan `order_id` dan `client_cache_id`.
5. Jika belum ada, sistem menyimpan `OfflineTrackSync` dengan status `synced`.
6. Mobile App menerima `sync_id` dan waktu sinkronisasi.

**Alur Alternatif:**
- Jika order bukan milik user, sistem mengembalikan `FORBIDDEN_ORDER_ACCESS`.
- Jika status order bukan `Sedang Mendaki`, sistem mengembalikan `ORDER_STATUS_NOT_SYNCABLE`.
- Jika GPX terlalu besar, sistem mengembalikan `GPX_TOO_LARGE`.
- Jika duplikat, sistem mengembalikan status sukses dengan `is_duplicate = true`.

**Kondisi Akhir:** Track offline tersimpan sebagai data GPX di backend.

## 10. Refund

**Nama Use Case:** Refund  
**Aktor Utama:** Pendaki dan Admin  
**Tujuan:** Pendaki mengajukan pembatalan/refund dan admin memprosesnya.  
**Prasyarat:** Pendaki login, order milik pendaki, transaksi sudah `Complete`, dan jalur mengizinkan refund.

**Alur Utama:**
1. Pendaki meminta preview refund ke `GET /api/refund-preview/{orderId}`.
2. Sistem memvalidasi kepemilikan order, status order, status transaksi, dan izin refund jalur.
3. `RefundCalculationService` menghitung nominal refund dan penalti.
4. Pendaki mengirim `POST /api/refund-requests`.
5. Sistem membuat `RefundRequest` berstatus `pending` dan mengubah order menjadi `Cancel Requested`.
6. Admin melihat daftar refund.
7. Admin menyetujui, menolak, atau menandai sudah ditransfer.
8. Jika ditandai `refunded`, order menjadi `Cancelled`.

**Alur Alternatif:**
- Jika order belum lunas, sistem menolak.
- Jika jalur tidak mengizinkan refund, sistem menolak.
- Jika admin menolak, order dikembalikan menjadi `Booking`.
- Jika nominal refund 0, sistem tetap dapat mencatat pembatalan sesuai hasil kalkulasi.

**Kondisi Akhir:** Refund request berstatus `pending`, `approved`, `rejected`, atau `refunded`.

