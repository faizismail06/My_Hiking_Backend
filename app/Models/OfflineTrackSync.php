<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflineTrackSync extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'client_cache_id',
        'source',
        'cached_at',
        'point_count',
        'distance_meters',
        'duration_seconds',
        'gpx_content',
        'sync_status',
        'synced_at',
    ];

    protected $casts = [
        'cached_at' => 'datetime',
        'synced_at' => 'datetime',
        'distance_meters' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
