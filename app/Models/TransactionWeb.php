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
        'total_bayar',
        'status_pesanan',
        'waktu_pembayaran',
        'bukti',
        'snap_token',
        'midtrans_order_id',
        'payment_type',
        'transaction_id',
        'transaction_time',
        'fraud_status',
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
     * Check if transaction is paid (Complete status)
     */
    public function isPaid()
    {
        return $this->status_pesanan === 'Complete';
    }

    /**
     * Event model untuk menentukan status pesanan sebelum menyimpan
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Jika status tidak Complete dan belum bayar, set status menjadi 'Incomplete'
            if ($model->status_pesanan !== 'Complete' && is_null($model->waktu_pembayaran)) {
                $model->status_pesanan = 'Incomplete';
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

    /**
     * Get formatted payment method name from payment_type
     */
    public function getPaymentMethodNameAttribute()
    {
        $methods = [
            'credit_card' => 'Kartu Kredit',
            'bank_transfer' => 'Transfer Bank',
            'bca_va' => 'BCA Virtual Account',
            'bni_va' => 'BNI Virtual Account',
            'bri_va' => 'BRI Virtual Account',
            'permata_va' => 'Permata Virtual Account',
            'cimb_va' => 'CIMB Niaga Virtual Account',
            'other_va' => 'Virtual Account Lainnya',
            'echannel' => 'Mandiri Bill Payment',
            'mandiri_bill' => 'Mandiri Bill Payment',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'qris' => 'QRIS',
            'indomaret' => 'Indomaret',
            'alfamart' => 'Alfamart',
        ];

        return $methods[$this->payment_type] ?? $this->payment_type ?? 'Belum dipilih';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'Complete' => 'Lunas',
            'Incomplete' => 'Belum Dibayar',
        ];

        return $labels[$this->status_pesanan] ?? $this->status_pesanan;
    }
}
