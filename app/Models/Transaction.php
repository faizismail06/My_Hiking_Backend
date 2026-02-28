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
        'payment_id',
        'total_bayar',
        'status_pesanan',
        'waktu_pembayaran',
        'bukti',
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

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
