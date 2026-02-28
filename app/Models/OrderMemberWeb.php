<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMemberWeb extends Model
{
    use HasFactory;

    protected $table = 'order_members';

    protected $fillable = [
        'id_pesanan',
        'id_users',
    ];

    public function user()
    {
        return $this->belongsTo(UserWeb::class, 'id_user', 'id'); 
    }

    public function order()
    {
        return $this->hasOne(OrderWeb::class, 'id_pesanan');
    }

    // Alias for backward compatibility
    public function pesanan()
    {
        return $this->order();
    }
}
