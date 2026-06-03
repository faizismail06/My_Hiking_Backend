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
        'jarak',
        'elevasi',
        'durasi',
        'tingkat_kesulitan',
        'deskripsi',
        'map_basecamp',
        'biaya',
        'daily_hiker_limit',
        'is_refund_allowed',
        'gambar_jalur',
        'latitude',
        'longitude',
        'route_points',
        'route_source',
        // DSS score columns
        'panorama_score',
        'fasilitas_score',
        'popularity_score',
        'safety_score',
        'crowd_level',
        'dss_status',
    ];

    protected $casts = [
        'gambar'            => 'array',
        'route_points'      => 'array',
        'jarak'             => 'float',
        'daily_hiker_limit' => 'integer',
        'is_refund_allowed' => 'boolean',
        // DSS numeric scores
        'panorama_score'    => 'float',
        'fasilitas_score'   => 'float',
        'popularity_score'  => 'float',
        'safety_score'      => 'float',
        'crowd_level'       => 'float',
        'dss_status'        => 'string',
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
