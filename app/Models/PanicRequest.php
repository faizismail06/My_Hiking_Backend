<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanicRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'latitude',
        'longitude',
        'emergency_type',
        'description',
        'status',
        'responded_by',
        'responded_at',
        'resolved_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'responded_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the user who sent the panic request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with this panic request.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the trail guard who responded to this panic request.
     */
    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Scope for pending panic requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for panic requests being responded to.
     */
    public function scopeResponding($query)
    {
        return $query->where('status', 'responding');
    }

    /**
     * Scope for resolved panic requests.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
