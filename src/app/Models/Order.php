<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    
    protected $table = "orders";

    protected $fillable = [
        'user_id',
        'order_date',
        'product_list',
        'status',
        'total_amount',
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
