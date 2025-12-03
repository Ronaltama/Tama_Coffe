<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    public $incrementing = false; // karena kamu pakai string id
    protected $keyType = 'string';

    protected $fillable = [
        'id','order_id','amount','method',
        'midtrans_transaction_id','midtrans_order_id','payment_type',
        'transaction_status','va_numbers','callback_payload',
        'status','date'
    ];

    protected $casts = [
        'va_numbers' => 'array',
        'callback_payload' => 'array',
        'amount' => 'decimal:2',
        'date' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class, 'order_id', 'id');
    }
}
