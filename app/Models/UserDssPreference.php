<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDssPreference extends Model
{
    protected $fillable = ['user_id', 'weight_key', 'weight_value'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
