# Class Diagram

Diagram berikut memodelkan entitas domain utama berdasarkan model Laravel, migration, serta tabel `chat_histories` yang digunakan Laravel/Flask chatbot.

```mermaid
classDiagram
    class User {
        +bigint id
        +string name
        +string email
        +string password
        +int level
        +string tier
        +string tier_source
        +string address
        +bigint nik
        +bigint phone
        +bigint emergency_phone
        +date date_of_birth
        +decimal total_earnings
        +decimal available_balance
    }

    class Mountain {
        +bigint id
        +string nama
        +text deskripsi
        +int ketinggian
        +string gambar_gunung
        +decimal latitude
        +decimal longitude
        +string province_id
        +string regency_id
        +string district_id
        +string village_id
    }

    class Route {
        +bigint id
        +bigint id_gunung
        +bigint user_id
        +string nama
        +int jarak
        +decimal elevasi
        +decimal durasi
        +string tingkat_kesulitan
        +int biaya
        +int daily_hiker_limit
        +boolean is_refund_allowed
        +json route_points
        +string route_source
        +float panorama_score
        +float fasilitas_score
        +float popularity_score
        +float safety_score
        +float crowd_level
        +string dss_status
    }

    class Order {
        +bigint id
        +bigint id_gunung
        +bigint id_jalur
        +bigint id_user
        +date tanggal_naik
        +date tanggal_turun
        +double total_harga_tiket
        +string status
        +datetime check_in
        +datetime check_out
    }

    class OrderMember {
        +bigint id
        +bigint id_pesanan
        +bigint id_user
    }

    class Transaction {
        +bigint id
        +bigint id_pesanan
        +int total_bayar
        +string status_pesanan
        +string payment_status
        +string payment_code
        +string payment_code_label
        +text payment_instruction
        +text deeplink_url
        +datetime waktu_pembayaran
        +string snap_token
        +string midtrans_order_id
        +string payment_type
        +string transaction_id
        +datetime transaction_time
        +string fraud_status
    }

    class PanicRequest {
        +bigint id
        +bigint user_id
        +bigint order_id
        +decimal latitude
        +decimal longitude
        +string emergency_type
        +text description
        +string status
        +bigint responded_by
        +datetime responded_at
        +datetime resolved_at
    }

    class RefundRequest {
        +bigint id
        +bigint order_id
        +bigint user_id
        +text cancel_reason
        +string refund_method
        +string bank_name
        +string account_number
        +string account_holder
        +decimal refund_amount
        +decimal penalty_amount
        +string refund_status
        +string proof_of_transfer
        +datetime requested_at
        +datetime processed_at
    }

    class UserExperience {
        +bigint id
        +bigint user_id
        +int jumlah_pendakian
        +int jumlah_summit
        +json questionnaire_answers
        +int weighted_score
        +string weighted_tier
        +datetime onboarding_completed_at
    }

    class UserDssPreference {
        +bigint id
        +bigint user_id
        +string weight_key
        +double weight_value
    }

    class Rule {
        +bigint id
        +bigint jalur_id
        +longText description
    }

    class TrailPost {
        +bigint id
        +bigint trail_id
        +string name
        +int sequence
        +decimal latitude
        +decimal longitude
        +decimal elevation
        +string icon_type
        +text description
    }

    class OfflineTrackSync {
        +bigint id
        +bigint order_id
        +bigint user_id
        +string client_cache_id
        +string source
        +datetime cached_at
        +int point_count
        +double distance_meters
        +int duration_seconds
        +longText gpx_content
        +string sync_status
        +datetime synced_at
    }

    class ChatHistory {
        +bigint id
        +bigint user_id
        +string role
        +string title
        +longText messages
    }

    class WithdrawalRequest {
        +bigint id
        +string user_id
        +decimal amount
        +decimal admin_fee
        +decimal net_amount
        +string withdrawal_method
        +string status
        +string approved_by
        +datetime approved_at
        +datetime completed_at
        +string transfer_proof_path
    }

    User "1" --> "0..*" Order : booker
    User "1" --> "0..*" Route : trailGuard
    User "1" --> "0..1" UserExperience : experience
    User "1" --> "0..*" UserDssPreference : preferences
    User "1" --> "0..*" PanicRequest : sends
    User "1" --> "0..*" RefundRequest : requests
    User "1" --> "0..*" OfflineTrackSync : syncs
    User "1" --> "0..*" ChatHistory : owns
    User "1" --> "0..*" WithdrawalRequest : requests

    Mountain "1" --> "0..*" Route : has
    Mountain "1" --> "0..*" Order : selected
    Route "1" --> "0..*" Order : booked
    Route "1" --> "0..*" Rule : rules
    Route "1" --> "0..*" TrailPost : posts

    Order "1" --> "0..*" OrderMember : members
    User "1" --> "0..*" OrderMember : member
    Order "1" --> "0..1" Transaction : transaction
    Order "1" --> "0..*" PanicRequest : emergency
    Order "1" --> "0..*" RefundRequest : refund
    Order "1" --> "0..*" OfflineTrackSync : tracks
```

## Catatan Class Diagram

- Model `Trail` menggunakan tabel `routes`; pada diagram diberi nama class `Route` agar konsisten dengan nama tabel dan domain jalur.
- `Order.check_in` dan `Order.check_out` digunakan oleh `TrailGuardController`, tetapi migration eksplisit penambahan kolom tersebut tidak ditemukan pada file yang dianalisis. Atribut ini perlu verifikasi pada skema database aktual.
- `OrderMember` model memiliki fillable `id_users`, sedangkan migration dan relasi memakai `id_user`. Ini perlu verifikasi karena dapat memengaruhi operasi model langsung.
- `WithdrawalRequest.user_id` dan `approved_by` pada migration bertipe string, sedangkan sebagian relasi mengarah ke `users.id`. Ini perlu verifikasi tipe data.

