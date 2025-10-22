<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'table_id', 'customer_name', 'customer_phone',
        'total_price', 'note', 'status'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function table() {
        return $this->belongsTo(Table::class);
    }

    public function orderDetails() {
        return $this->hasMany(OrderDetail::class);
    }

    public function payment() {
        return $this->hasOne(Payment::class);
    }
}