<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    protected $table = 'reg_districts'; // Tabel untuk districts

    protected $fillable = [
        'name', // Kolom untuk nama provinsi
    ];
}
