<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trade extends Model
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
        'is_buyer',
        'is_maker',
        'is_best_match'
    ];


    public static function createTrade($data = [])
    {
        return self::create($data);
    }
}
