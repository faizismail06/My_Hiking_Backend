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
}
