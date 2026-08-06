<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mountain extends Model
{
    use HasFactory;

    protected $table = 'mountains';

    protected $fillable = [
        'nama',
        'province_id',
        'deskripsi',
        'ketinggian',
        'gambar_gunung',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'gambar' => 'array',
    ];


    // Relasi many-to-many dengan Trail
    public function trails()
    {
        return $this->hasMany(Trail::class, 'id_gunung', 'id')->distinct();
    }

    // Alias for backward compatibility
    public function jalur()
    {
        return $this->trails();
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }
}
