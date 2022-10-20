<?php

namespace App\Http\Controllers;

use App\Models\ApiPair;
use App\Models\CustomTrade;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        /* @var $user User */
        $user = \auth()->user();
        $user_trades = CustomTrade::where('user_id', '=', $user->id)->orderByDesc('created_at')->get();
        $pairs = ApiPair::where('user_id', '=', $user->id)->get();
        foreach ($user_trades as $trade)
        {
            $trade->api_pair_name = $trade->apiPair->name;
            $trade->open_date = $trade->created_at->format('H:i:s d-m-Y');
            $date_close = $trade->updated_at->format('H:i:s d-m-Y');
            if($trade->price_sell == 0) $date_close = 'NOT YET SOLD';
            $trade->close_date = $date_close;
        }

        return Inertia::render('Dashboard/Index', [
            'trades' => $user_trades,
            'pairs' => $pairs
        ]);
    }
}
