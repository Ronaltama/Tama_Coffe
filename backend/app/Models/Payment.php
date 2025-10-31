<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id', 'order_id', 'amount', 'method', 'proof', 'status', 'date'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
