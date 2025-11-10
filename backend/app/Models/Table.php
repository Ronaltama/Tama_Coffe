<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Table extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id', 'table_number', 'capacity', 'type', 'qr_code_url', 'status'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'table_id');
    }
}
