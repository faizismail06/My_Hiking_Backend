<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Menentukan bahwa ID akan bertipe string (UUID) dan tidak auto-increment
    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'orders';

    protected $fillable = [
        'id_gunung',
        'id_jalur',
        'id_user',
        'tanggal_naik',
        'tanggal_turun',
        'total_harga_tiket',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = self::generateUniqueID();
        });
    }

    protected static function generateUniqueID()
    {
        do {
            $id = mt_rand(1000000000, 9999999999); // Hasilkan angka 10 digit
        } while (self::where('id', $id)->exists());

        return $id;
    }

    public function mountain()
    {
        return $this->belongsTo(Mountain::class, 'id_gunung');
    }

    // Alias for backward compatibility
    public function gunung()
    {
        return $this->mountain();
    }

    public function trail()
    {
        return $this->belongsTo(Trail::class, 'id_jalur');
    }

    // Alias for backward compatibility
    public function jalur()
    {
        return $this->trail();
    }

    // Relasi ke tabel `users` (pemesan utama / main booker)
    public function booker()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Alias for backward compatibility
    public function pemesan()
    {
        return $this->booker();
    }

    // Relasi ke tabel `order_members` (anggota tambahan)
    public function members()
    {
        return $this->belongsToMany(User::class, 'order_members', 'id_pesanan', 'id_user');
    }

    // Alias for backward compatibility
    public function anggota()
    {
        return $this->members();
    }

    // Relasi ke model transaksi
    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'id_pesanan');
    }

    // Alias for backward compatibility
    public function transaksi()
    {
        return $this->transaction();
    }
}
