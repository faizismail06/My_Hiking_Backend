<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JalurWeb extends Model
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
        'jarak',
        'deskripsi',
        'map_basecamp',
        'biaya',
        'gambar_jalur'
    ];

    // Relasi dengan model Gunung
    public function gunung()
    {
        return $this->belongsTo(GunungWeb::class, 'id_gunung', 'id');
    }
    
    public function province()
    {
        return $this->belongsTo(ProvinceWeb::class, 'province_id');
    }

    public function regency()
    {
        return $this->belongsTo(RegencyWeb::class, 'regency_id');
    }
    public function district()
    {
        return $this->belongsTo(DistrictWeb::class, 'district_id');
    }
    public function village()
    {
        return $this->belongsTo(VillageWeb::class, 'village_id');
    }
    public function pesanan()
    {
        return $this->hasMany(PesananWeb::class, 'id_jalur', 'id')->distinct();
    }
}