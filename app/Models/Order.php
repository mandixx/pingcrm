<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'symbol',
        'trade_id',
        'order_id',
        'order_list_id',
        'price',
        'qty',
        'commission',
        'side',
    ];

    public static function createOrder($data = [])
    {
        return self::create($data);
    }
}
