<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiPair extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'strategy_id',
        'name',
        'api_key',
        'api_secret'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function strategy() {
        return $this->belongsTo(Strategy::class);
    }

    public function customTrades() {
        return $this->hasMany(CustomTrade::class, 'api_pair_id');
    }

}
