<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MountainWeb extends Model
{
    use HasFactory;

    protected $table = 'mountains';

    protected $fillable = [
        'nama',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'ketinggian',
        'deskripsi',
        'gambar_gunung',
    ];

    // Relasi dengan Trail
    public function trails()
    {
        return $this->hasMany(TrailWeb::class, 'id_gunung', 'id')->distinct();
    }

    // Alias for backward compatibility
    public function jalur()
    {
        return $this->trails();
    }

    public function province()
    {
        return $this->belongsTo(ProvinceWeb::class, 'province_id', 'id');
    }


    // Relasi ke model Regency
    public function regency()
    {
        return $this->belongsTo(RegencyWeb::class, 'regency_id');
    }

    // Relasi ke model District
    public function district()
    {
        return $this->belongsTo(DistrictWeb::class, 'district_id');
    }

    // Relasi ke model Village
    public function village()
    {
        return $this->belongsTo(VillageWeb::class, 'village_id');
    }
}
