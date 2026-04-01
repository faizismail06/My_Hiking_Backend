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
        'elevasi',
        'durasi',
        'tingkat_kesulitan',
        'deskripsi',
        'map_basecamp',
        'biaya',
        'gambar_jalur',
        'latitude',
        'longitude',
        'route_points',
        'route_source',
    ];

    protected $casts = [
        'route_points' => 'array',
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

    public function posts()
    {
        return $this->hasMany(TrailPost::class, 'trail_id')->orderBy('sequence');
    }

    // Alias for backward compatibility
    public function pesanan()
    {
        return $this->orders();
    }
}
