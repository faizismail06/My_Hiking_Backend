<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrailWeb extends Model
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
        'jarak',
        'deskripsi',
        'map_basecamp',
        'biaya',
        'gambar_jalur'
    ];

    // Relasi dengan model Mountain
    public function mountain()
    {
        return $this->belongsTo(MountainWeb::class, 'id_gunung', 'id');
    }

    // Alias for backward compatibility
    public function gunung()
    {
        return $this->mountain();
    }

    // Relasi dengan model User (trail guard)
    public function trailGuard()
    {
        return $this->belongsTo(UserWeb::class, 'user_id', 'id');
    }

    // Alias for backward compatibility
    public function penjaga()
    {
        return $this->trailGuard();
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
    public function orders()
    {
        return $this->hasMany(OrderWeb::class, 'id_jalur', 'id')->distinct();
    }

    // Alias for backward compatibility
    public function pesanan()
    {
        return $this->orders();
    }
}
