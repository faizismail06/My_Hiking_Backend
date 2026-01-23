<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Menentukan bahwa ID akan bertipe string (UUID) dan tidak auto-increment
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'address',
        'nik',
        'phone',
        'emergency_phone',
        'level',
        'profile_picture',
        'date_of_birth',
    ];

    /**
    * The attributes that should be hidden for serialization.
    *
    * @var array<int, string>
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
    ];

    /**
     * Boot method untuk UUID pada ID.
     */
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

     // Relasi ke tabel `pesanan` (user sebagai pemesan utama)
    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'user_id');
    }

    // Relasi ke tabel `anggota_pesanan` (user sebagai anggota pemesanan)
    public function anggotaPesanan()
    {
        return $this->belongsToMany(Pesanan::class, 'anggota_pesanan', 'user_id', 'pesanan_id');
    }
}
