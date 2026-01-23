<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaPesanan extends Model
{
    use HasFactory;

    protected $table = 'anggota_pesanan';

    protected $fillable = [
        'id_pesanan',
        'id_users',
    ];

    public function user()
    {
        return $this->belongsToMany(User::class, 'anggota_pesanan', 'id_pesanan', 'id_user');
    }

    public function pesanan()
    {
        return $this->hasOne(Pesanan::class, 'id_pesanan');
    }
}
