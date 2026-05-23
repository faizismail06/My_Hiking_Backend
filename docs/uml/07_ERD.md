# Entity Relationship Diagram

ERD berikut berfokus pada tabel utama aplikasi. Tabel framework seperti `failed_jobs`, `password_reset_tokens`, `personal_access_tokens`, dan `notifications` tidak dimasukkan ke diagram utama.

```mermaid
erDiagram
    users {
        BIGINT id PK
        VARCHAR name
        VARCHAR email
        VARCHAR password
        INT level
        ENUM tier
        ENUM tier_source
        VARCHAR address
        BIGINT nik
        BIGINT phone
        BIGINT emergency_phone
        VARCHAR profile_picture
        DATE date_of_birth
        DECIMAL total_earnings
        DECIMAL withdrawn_amount
        DECIMAL available_balance
    }

    mountains {
        BIGINT id PK
        CHAR province_id FK
        CHAR regency_id FK
        CHAR district_id FK
        CHAR village_id FK
        VARCHAR nama
        TEXT deskripsi
        INT ketinggian
        VARCHAR gambar_gunung
        DECIMAL latitude
        DECIMAL longitude
    }

    routes {
        BIGINT id PK
        VARCHAR nama
        BIGINT id_gunung FK
        BIGINT user_id FK
        CHAR province_id FK
        CHAR regency_id FK
        CHAR district_id FK
        CHAR village_id FK
        INT jarak
        DECIMAL elevasi
        DECIMAL durasi
        ENUM tingkat_kesulitan
        TEXT deskripsi
        VARCHAR map_basecamp
        VARCHAR gambar_jalur
        INT biaya
        INT daily_hiker_limit
        BOOLEAN is_refund_allowed
        DECIMAL latitude
        DECIMAL longitude
        JSON route_points
        VARCHAR route_source
        FLOAT panorama_score
        FLOAT fasilitas_score
        FLOAT popularity_score
        FLOAT safety_score
        FLOAT crowd_level
        VARCHAR dss_status
    }

    orders {
        BIGINT id PK
        BIGINT id_gunung FK
        BIGINT id_jalur FK
        BIGINT id_user FK
        DATE tanggal_naik
        DATE tanggal_turun
        DOUBLE total_harga_tiket
        ENUM status
        DATETIME check_in
        DATETIME check_out
    }

    order_members {
        BIGINT id PK
        BIGINT id_pesanan FK
        BIGINT id_user FK
    }

    transactions {
        BIGINT id PK
        BIGINT id_pesanan FK
        INT total_bayar
        ENUM status_pesanan
        ENUM payment_status
        VARCHAR payment_code
        VARCHAR payment_code_label
        TEXT payment_instruction
        TEXT deeplink_url
        DATETIME waktu_pembayaran
        VARCHAR bukti
        VARCHAR snap_token
        VARCHAR midtrans_order_id
        VARCHAR payment_type
        VARCHAR transaction_id
        DATETIME transaction_time
        VARCHAR fraud_status
    }

    panic_requests {
        BIGINT id PK
        BIGINT user_id FK
        BIGINT order_id FK
        DECIMAL latitude
        DECIMAL longitude
        VARCHAR emergency_type
        TEXT description
        ENUM status
        BIGINT responded_by FK
        TIMESTAMP responded_at
        TIMESTAMP resolved_at
    }

    refund_requests {
        BIGINT id PK
        BIGINT order_id FK
        BIGINT user_id FK
        TEXT cancel_reason
        VARCHAR refund_method
        VARCHAR bank_name
        VARCHAR account_number
        VARCHAR account_holder
        DECIMAL refund_amount
        DECIMAL penalty_amount
        ENUM refund_status
        VARCHAR proof_of_transfer
        TIMESTAMP requested_at
        TIMESTAMP processed_at
    }

    user_experiences {
        BIGINT id PK
        BIGINT user_id FK
        INT jumlah_pendakian
        INT jumlah_summit
        JSON questionnaire_answers
        INT weighted_score
        VARCHAR weighted_tier
        TIMESTAMP onboarding_completed_at
    }

    user_dss_preferences {
        BIGINT id PK
        BIGINT user_id FK
        VARCHAR weight_key
        DOUBLE weight_value
    }

    rules {
        BIGINT id PK
        BIGINT jalur_id FK
        LONGTEXT description
    }

    trail_posts {
        BIGINT id PK
        BIGINT trail_id FK
        VARCHAR name
        INT sequence
        DECIMAL latitude
        DECIMAL longitude
        DECIMAL elevation
        VARCHAR icon_type
        TEXT description
    }

    offline_track_syncs {
        BIGINT id PK
        BIGINT order_id FK
        BIGINT user_id FK
        VARCHAR client_cache_id
        VARCHAR source
        TIMESTAMP cached_at
        INT point_count
        DOUBLE distance_meters
        INT duration_seconds
        LONGTEXT gpx_content
        ENUM sync_status
        TIMESTAMP synced_at
    }

    withdrawal_requests {
        BIGINT id PK
        VARCHAR user_id
        DECIMAL amount
        DECIMAL admin_fee
        DECIMAL net_amount
        ENUM withdrawal_method
        VARCHAR bank_name
        VARCHAR account_number
        VARCHAR account_holder
        VARCHAR e_wallet_type
        VARCHAR e_wallet_number
        ENUM status
        TEXT rejection_reason
        VARCHAR approved_by
        TIMESTAMP approved_at
        TIMESTAMP completed_at
        VARCHAR transfer_proof_path
    }

    chat_histories {
        BIGINT id PK
        BIGINT user_id FK
        VARCHAR role
        VARCHAR title
        LONGTEXT messages
    }

    users ||--o{ orders : books
    users ||--o{ routes : guards
    users ||--o| user_experiences : has
    users ||--o{ user_dss_preferences : sets
    users ||--o{ order_members : joins
    users ||--o{ panic_requests : sends
    users ||--o{ refund_requests : requests
    users ||--o{ offline_track_syncs : syncs
    users ||--o{ chat_histories : owns

    mountains ||--o{ routes : has
    mountains ||--o{ orders : selected
    routes ||--o{ orders : booked
    routes ||--o{ rules : has
    routes ||--o{ trail_posts : has

    orders ||--o{ order_members : includes
    orders ||--o| transactions : paid_by
    orders ||--o{ panic_requests : has
    orders ||--o{ refund_requests : has
    orders ||--o{ offline_track_syncs : has
```

## Catatan ERD

- `chat_histories` ada pada migration Laravel dan juga dibuat otomatis oleh Flask `database.py` jika belum ada.
- `withdrawal_requests` relevan untuk fitur penjaga dan admin earnings, sehingga dimasukkan.
- Kolom `check_in` dan `check_out` digunakan oleh controller web penjaga, tetapi migration eksplisitnya tidak ditemukan pada file yang dianalisis; perlu verifikasi.
- Tabel wilayah `reg_provinces`, `reg_regencies`, `reg_districts`, dan `reg_villages` menjadi referensi lokasi, tetapi tidak ditampilkan agar ERD utama tetap fokus.

