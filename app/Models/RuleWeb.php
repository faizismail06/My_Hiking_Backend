<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuleWeb extends Model
{
    use HasFactory;

    protected $table = 'rules';

    protected $fillable = [
        'jalur_id',
        'description'
    ];

    public function trail()
    {
        return $this->belongsTo(TrailWeb::class, 'jalur_id');
    }

    // Alias for backward compatibility
    public function jalur()
    {
        return $this->trail();
    }
}
