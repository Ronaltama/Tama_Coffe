<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id', 'table_id', 'order_id', 'name', 'phone',
        'date', 'time', 'booking_code'
    ];

    // No need for items cast anymore
    // Items data available via order->orderDetails relationship

    public function user()
    {
        // Remove this relation since we don't have user_id anymore
        return null;
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
