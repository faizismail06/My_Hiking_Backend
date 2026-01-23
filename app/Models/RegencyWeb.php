<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegencyWeb extends Model
{
    use HasFactory;
    protected $table = 'reg_regencies'; // Tabel untuk regency

    protected $fillable = [
        'name', // Kolom untuk nama provinsi
    ];
    public function districts()
    {
        return $this->hasMany(DistrictWeb::class);
    }
}
