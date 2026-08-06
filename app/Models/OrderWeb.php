<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderWeb extends Model
{
    use HasFactory;

    // Nama tabel yang terkait
    protected $table = 'orders';

    // Primary key dari tabel
    protected $primaryKey = 'id';

    // Kolom yang dapat diisi (mass assignable)
    protected $fillable = [
        'id_gunung',
        'id_jalur',
        'id_anggota_pesanan',
        'tanggal_naik',
        'tanggal_turun',
        'total_harga_tiket',
        'status',
    ];

    // Nonaktifkan timestamps jika tidak digunakan
    public $timestamps = true;

    // Casting tipe data untuk kolom tertentu
    protected $casts = [
        'tanggal_naik' => 'datetime',
        'tanggal_turun' => 'datetime',
    ];

    /**
     * Relasi ke model Mountain
     */
    public function mountain()
    {
        return $this->belongsTo(MountainWeb::class, 'id_gunung', 'id', 'nama');
    }

    // Alias for backward compatibility
    public function gunung()
    {
        return $this->mountain();
    }

    /**
     * Relasi ke model Trail
     */
    public function trail()
    {
        return $this->belongsTo(TrailWeb::class, 'id_jalur', 'id', 'nama');
    }

    // Alias for backward compatibility
    public function jalur()
    {
        return $this->trail();
    }

    /**
     * Relasi ke model OrderMember
     */
    public function orderMembers()
    {
        return $this->hasMany(OrderMemberWeb::class, 'id_pesanan');
    }

    // Alias for backward compatibility
    public function anggotaPesanan()
    {
        return $this->orderMembers();
    }

    // Relasi Order ke User (Booker)
    public function user()
    {
        return $this->belongsTo(UserWeb::class, 'id_user');
    }

    /**
     * Mendapatkan status pesanan dengan label yang lebih mudah dimengerti
     */
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'Booking':
                return 'Booking';
            case 'Cancel Requested':
                return 'Cancel Requested';
            case 'Cancelled':
                return 'Cancelled';
            case 'Sedang Mendaki':
                return 'Sedang Mendaki';
            case 'Selesai':
                return 'Selesai';
            case 'Expired':
                return 'Expired';
            default:
                return $this->status;
        }
    }

    /**
     * Relasi ke model Transaction
     */
    public function transaction()
    {
        return $this->hasOne(TransactionWeb::class, 'id_pesanan');
    }

    // Alias for backward compatibility
    public function transaksi()
    {
        return $this->transaction();
    }

    /**
     * Relasi ke model OfflineTrackSync
     */
    public function offlineTrackSyncs()
    {
        return $this->hasMany(OfflineTrackSync::class, 'order_id');
    }

    /**
     * Relasi ke model PanicRequest
     */
    public function panicRequests()
    {
        return $this->hasMany(PanicRequest::class, 'order_id');
    }
}
