<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'id_pesanan',
        'total_bayar',
        'status_pesanan',
        'waktu_pembayaran',
        'bukti',
        // Midtrans fields
        'snap_token',
        'midtrans_order_id',
        'payment_type',
        'transaction_id',
        'transaction_time',
        'fraud_status',
    ];

    protected $casts = [
        'waktu_pembayaran' => 'datetime',
        'transaction_time' => 'datetime',
    ];

    // Relasi ke model Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'id_pesanan');
    }

    // Alias for backward compatibility
    public function pesanan()
    {
        return $this->order();
    }

    /**
     * Get payment method name from Midtrans payment_type
     */
    public function getPaymentMethodNameAttribute()
    {
        $methods = [
            'credit_card' => 'Kartu Kredit/Debit',
            'bank_transfer' => 'Transfer Bank',
            'echannel' => 'Mandiri Bill',
            'bca_klikpay' => 'BCA KlikPay',
            'bca_klikbca' => 'KlikBCA',
            'bri_epay' => 'BRI e-Pay',
            'cimb_clicks' => 'CIMB Clicks',
            'danamon_online' => 'Danamon Online',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'qris' => 'QRIS',
            'indomaret' => 'Indomaret',
            'alfamart' => 'Alfamart',
            'akulaku' => 'Akulaku',
            'kredivo' => 'Kredivo',
        ];

        return $methods[$this->payment_type] ?? $this->payment_type ?? 'Belum dipilih';
    }

    /**
     * Check if payment is completed
     */
    public function isPaid()
    {
        return $this->status_pesanan === 'Complete';
    }

    /**
     * Check if payment is pending
     */
    public function isPending()
    {
        return $this->status_pesanan === 'Incomplete';
    }

    /**
     * Get payment status label
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
