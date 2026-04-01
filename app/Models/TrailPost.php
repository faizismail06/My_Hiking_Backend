<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrailPost extends Model
{
    use HasFactory;

    protected $table = 'trail_posts';

    protected $fillable = [
        'trail_id',
        'name',
        'sequence',
        'latitude',
        'longitude',
        'elevation',
        'icon_type',
        'description',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'elevation' => 'float',
    ];

    public function trail()
    {
        return $this->belongsTo(Trail::class, 'trail_id');
    }
}
