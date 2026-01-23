<?php
// Model Province
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvinceWeb extends Model
{
    use HasFactory;

    protected $table = 'reg_provinces'; // Pastikan nama tabel benar
    protected $fillable = ['name']; // Kolom yang bisa diisi mass assignment

    // Relasi ke model Gunung
    public function gunungs()
    {
        return $this->hasMany(GunungWeb::class, 'province_id'); // Menghubungkan Gunung dengan Province
    }
    public function regencies()
    {
        return $this->hasMany(RegencyWeb::class);
    }
}
