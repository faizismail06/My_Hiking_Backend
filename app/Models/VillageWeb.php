<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VillageWeb extends Model
{
    use HasFactory;
    protected $table = 'reg_villages'; // Tabel untuk provinsi

    protected $fillable = [
        'name', // Kolom untuk nama provinsi
    ];
}
