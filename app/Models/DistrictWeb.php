<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistrictWeb extends Model
{
    use HasFactory;
    protected $table = 'reg_districts'; // Tabel untuk districts

    protected $fillable = [
        'name', // Kolom untuk nama provinsi
    ];
    public function villages()
    {
        return $this->hasMany(VillageWeb::class);
    }
}
