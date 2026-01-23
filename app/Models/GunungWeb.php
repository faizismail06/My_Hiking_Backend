<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GunungWeb extends Model
{
    use HasFactory;

    protected $table = 'gunung';

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

    // Relasi many-to-many dengan Jalur melalui tabel pivot gunung_jalur
    public function jalur()
    {
        return $this->hasMany(JalurWeb::class, 'id_gunung', 'id')->distinct(); // Tambahkan distinct untuk menghindari duplikasi
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
    public function pesanan()
    {
        return $this->hasMany(PesananWeb::class, 'id_gunung', 'id')->distinct();
    }
}