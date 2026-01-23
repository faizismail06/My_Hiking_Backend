<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gunung extends Model
{
    use HasFactory;

    protected $table = 'gunung';

    protected $fillable = [
        'nama',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'deskripsi',
        'ketinggian',
        // 'gambar',
    ];

    protected $casts = [
        'gambar' => 'array',
    ];


    // Relasi many-to-many dengan Jalur melalui tabel pivot gunung_jalur
    public function jalur()
    {
        return $this->hasMany(Jalur::class, 'id_gunung', 'id')->distinct(); // Tambahkan distinct untuk menghindari duplikasi
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }


    // Relasi ke model Regency
    public function regency()
    {
        return $this->belongsTo(Regency::class, 'regency_id');
    }

    // Relasi ke model District
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    // Relasi ke model Village
    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id');
    }

    public function pesanan()
    {
        return $this->hasOne(Pesanan::class, 'user_id');
    }
}
