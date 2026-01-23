<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jalur extends Model
{
    use HasFactory;

    protected $table = 'jalur';

    protected $fillable = [
        'id_gunung',
        'nama',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'deskripsi',
        'map_basecamp',
        'biaya'
    ];
    protected $casts = [
        'gambar' => 'array',
    ];
    // Relasi dengan model Gunung
    public function gunung()
    {
        return $this->belongsTo(Gunung::class, 'id_gunung', 'id');
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

    public function pesanan()
    {
        return $this->hasOne(Pesanan::class, 'user_id');
    }
    public function tataTertibs()
    {
        return $this->hasMany(TataTertib::class);
    }
}
