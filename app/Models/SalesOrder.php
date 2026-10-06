<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        'customer_id',
        'order_date',
        'required_date',
    ];

    protected $casts = [
        'order_date' => 'date',
        'required_date' => 'date',
    ];
}