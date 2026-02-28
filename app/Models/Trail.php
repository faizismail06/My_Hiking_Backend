<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trail extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'id_gunung',
        'nama',
        'user_id',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'deskripsi',
        'map_basecamp',
        'biaya',
        'gambar_jalur',
        'latitude',
        'longitude',
    ];
    protected $casts = [
        'gambar' => 'array',
    ];
    
    // Relasi dengan model Mountain
    public function mountain()
    {
        return $this->belongsTo(Mountain::class, 'id_gunung', 'id');
    }

    // Alias for backward compatibility
    public function gunung()
    {
        return $this->mountain();
    }

    // Relasi dengan model User (penjaga jalur / trail guard)
    public function trailGuard()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Alias for backward compatibility
    public function penjaga()
    {
        return $this->trailGuard();
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class, 'regency_id');
    }
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }
    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id');
    }

    public function orders()
    {
        return $this->hasOne(Order::class, 'user_id');
    }

    // Alias for backward compatibility
    public function pesanan()
    {
        return $this->orders();
    }
}
