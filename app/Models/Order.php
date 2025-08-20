<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $casts = [
        'items' => 'array',
        'total_price' => 'decimal:2',
    ];
    protected $fillable = ['customer_name', 'items', 'total_price', 'status'];
}