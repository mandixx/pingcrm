<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomTrade extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'api_pair_id',
        'symbol',
        'buy_order_id',
        'sell_order_id',
        'order_list_id',
        'price_buy',
        'price_sell',
        'qty',
        'commission',
        'profit',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function apiPair() {
        return $this->belongsTo(ApiPair::class);
    }

    public static function createCustomTrade($data = [])
    {
        return self::create($data);
    }
}
