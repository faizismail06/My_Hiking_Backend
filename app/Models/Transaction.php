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
        'payment_status',
        'payment_code',
        'payment_code_label',
        'payment_instruction',
        'deeplink_url',
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

    protected $attributes = [
        'status_pesanan' => 'Incomplete',
        'payment_status' => 'pending',
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
        return $this->normalizedPaymentStatus() === 'paid';
    }

    /**
     * Check if payment is pending
     */
    public function isPending()
    {
        return $this->normalizedPaymentStatus() === 'pending';
    }

    public function normalizedPaymentStatus(): string
    {
        $status = strtolower((string) ($this->payment_status ?? ''));

        if (in_array($status, ['pending', 'paid', 'expired', 'failed'], true)) {
            return $status;
        }

        return strtolower((string) $this->status_pesanan) === 'complete'
            ? 'paid'
            : 'pending';
    }

    /**
     * Get payment status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'Complete' => 'Lunas',
            'Incomplete' => 'Belum Dibayar',
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Lunas',
            'expired' => 'Kedaluwarsa',
            'failed' => 'Gagal',
        ];

        $status = $this->payment_status ?? $this->status_pesanan;
        return $labels[$status] ?? $status;
    }
}
