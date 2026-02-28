<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
    ];

    /**
     * Get the user who initiated the friend request
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the friend (user who received the request)
     */
    public function friend()
    {
        return $this->belongsTo(User::class, 'friend_id');
    }
}
