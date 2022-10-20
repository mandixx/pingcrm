<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Strategy extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name'
    ];

    public function apiPairs()
    {
        return $this->hasMany(ApiPair::class);
    }

    /**
     * Write code on Method
     *
     */
    public function generateUniqueCode()
    {
        do {
            $code = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(15/strlen($x)) )),1,15);
        } while (Strategy::where("code", "=", $code)->first());

        return $code;
    }
}
