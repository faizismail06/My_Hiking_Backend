<?php

namespace App\Models;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['nama_pembayaran', 'nomor_pembayaran', 'gambar_pembayaran'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'payment_id'); // Relasi One-to-Many
    }
}
