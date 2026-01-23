<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['nama_pembayaran', 'nomor_pembayaran', 'gambar_pembayaran'];

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'payment_id'); // Relasi One-to-Many
    }
}
