<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionWeb extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'transactions';

    // Kolom yang dapat diisi secara mass assignment
    protected $fillable = [
        'id_pesanan',
        'payment_id',
        'total_bayar',
        'status_pesanan',
        'waktu_pembayaran',
        'bukti',
    ];

    // Nilai default untuk status pesanan
    protected $attributes = [
        'status_pesanan' => 'Incomplete',
    ];

    /**
     * Relasi ke model Order
     */
    public function order()
    {
        return $this->belongsTo(OrderWeb::class, 'id_pesanan');
    }

    // Alias for backward compatibility
    public function pesanan()
    {
        return $this->order();
    }

    /**
     * Relasi ke model Payment
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * Event model untuk menentukan status pesanan sebelum menyimpan
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Jika bukti dan waktu pembayaran NULL, set status menjadi 'Incomplete'
            if (is_null($model->waktu_pembayaran) || is_null($model->bukti)) {
                $model->status_pesanan = 'Incomplete';
            }
            // Jika semua data valid tapi belum diverifikasi, set status menjadi 'Unverified'
            else if ($model->status_pesanan !== 'Verified') {
                $model->status_pesanan = 'Unverified';
            }
        });
    }

    /**
     * Scope untuk transaksi dengan metode pembayaran tertentu
     */
    public function scopePaymentMethod($query, $method)
    {
        return $query->where('metode_pembayaran', $method);
    }

    /**
     * Accessor untuk format waktu pembayaran
     */
    public function getFormattedPaymentTimeAttribute()
    {
        return date('d-m-Y H:i:s', strtotime($this->waktu_pembayaran));
    }

    /**
     * Menampilkan bukti pembayaran sebagai URL gambar
     */
    public function getProofUrlAttribute()
    {
        if ($this->bukti) {
            return asset('storage/bukti/' . $this->bukti);
        }
        return asset('images/no-image.png');
    }
}
