<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMember extends Model
{
    use HasFactory;

    protected $table = 'order_members';

    protected $fillable = [
        'id_pesanan',
        'id_users',
    ];

    public function user()
    {
        return $this->belongsToMany(User::class, 'order_members', 'id_pesanan', 'id_user');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'id_pesanan');
    }

    // Alias for backward compatibility
    public function pesanan()
    {
        return $this->order();
    }
}
