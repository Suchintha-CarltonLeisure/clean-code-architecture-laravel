<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $casts = ['items' => 'array'];
    protected $fillable = ['customer_name', 'items', 'total_price', 'status'];
}