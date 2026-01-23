<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TataTertib extends Model
{
    protected $fillable = [
        'jalur_id',
        'description'
    ];

    public function jalur()
    {
        return $this->belongsTo(Jalur::class);
    }
}
