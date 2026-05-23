# Indeks Dokumentasi UML

Dokumentasi ini disusun berdasarkan source code yang tersedia pada workspace:

- Laravel Backend API, Web Admin, dan Web Penjaga Basecamp pada folder `Backend/My_Hiking_Backend`.
- Flask RAG Chatbot Service pada folder saudara `My_Hiking_Chatbot`.
- Source code Flutter Mobile App tidak ditemukan di workspace yang dianalisis, sehingga nama screen/bloc pada diagram mobile bersifat boundary konseptual dan diberi catatan perlu verifikasi.

## Daftar Dokumen

| File | Isi Utama |
| --- | --- |
| `01_USE_CASE_DIAGRAM.md` | Aktor sistem dan diagram use case berbasis Mermaid flowchart. |
| `02_USE_CASE_SCENARIOS.md` | Skenario tekstual sebelum diagram untuk fitur utama. |
| `03_ACTIVITY_DIAGRAMS.md` | Activity diagram umum dengan swimlane berbasis subgraph. |
| `04_SEQUENCE_DIAGRAMS.md` | Sequence diagram boundary-control-entity untuk fitur utama. |
| `05_CLASS_DIAGRAM.md` | Class diagram model/domain utama Laravel dan chatbot. |
| `06_DEPLOYMENT_DIAGRAM.md` | Deployment diagram sistem mobile, web, backend, chatbot, database, dan layanan eksternal. |
| `07_ERD.md` | ERD Mermaid berdasarkan migration/model utama Laravel dan tabel chatbot. |
| `08_VALIDATION_NOTES.md` | Catatan validasi fitur, endpoint, file rujukan, dan batasan. |

## Fitur yang Dicakup

- Autentikasi login reguler dan Google OAuth.
- Booking tiket pendakian, anggota pesanan, validasi kuota harian, dan validasi DSS.
- Pembayaran Midtrans, status pembayaran, callback notifikasi, QRIS/proxy QR.
- DSS rekomendasi jalur: ranking TOPSIS dan risk assessment rule-based berbasis tier, cuaca, dan tingkat kesulitan jalur.
- Chatbot RAG informasi berbasis data MySQL dengan context injection ke Gemini API.
- Chatbot booking melalui Flask `tools.py` menuju Laravel API.
- Chatbot CRUD admin untuk gunung dan jalur, dengan catatan ketidaksesuaian endpoint.
- QR check-in/check-out oleh penjaga jalur melalui web.
- Panic button dan dashboard SAR penjaga jalur.
- Offline tracking dan sync GPX.
- Refund pendaki dan approval/refunded oleh admin.

## Catatan Umum

1. RAG yang ditemukan adalah database-grounded RAG atau structured-data RAG. Retrieval dilakukan dari MySQL melalui query terstruktur pada `database.py` dan `context_builders.py`, lalu konteks disisipkan ke system prompt Gemini pada `gemini_engine.py`.
2. DSS rekomendasi jalur memakai TOPSIS pada `TopsisService`, sedangkan evaluasi risiko booking memakai rule-based risk assessment pada `DSSService`.
3. Endpoint CRUD chatbot admin untuk `/api/mountains`, `/api/routes`, dan alias `/api/trails` perlu verifikasi. Route API Laravel yang terbukti hanya menyediakan `GET /api/mountains`; tidak ditemukan `POST/PUT/DELETE /api/mountains`, `POST/PUT/DELETE /api/routes`, atau alias `/api/trails`.
4. Tabel/framework seperti `failed_jobs`, `password_reset_tokens`, `personal_access_tokens`, dan `notifications` tidak dimasukkan ke ERD utama.

