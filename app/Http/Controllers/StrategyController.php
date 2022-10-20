<?php

namespace App\Http\Controllers;

use App\Models\ApiPair;
use App\Models\Strategy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

class StrategyController extends Controller
{
    //
    /**
     * Update Strategies
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(Request $request, $user_id)
    {
        //
        try {
            $user = User::findOrFail($user_id);
            $strategies = (array)json_decode($request->getContent());
            foreach($strategies['strategies'] as $strategy) {
                $strategy = (array) $strategy;
                unset($strategy['ind']);
                if(!isset($strategy['id']))
                    $strategy['id'] = null;

                Strategy::updateOrCreate(
                    ['id' => $strategy['id']],
                    ['name' => $strategy['name']]
                );
            }

            return Redirect::back()->with('strategies', $user->strategies);

        } catch (\Exception $ex) {
            return Response::json([
                'message' => $ex->getMessage()
            ], 400);
        }
    }

    /**
     * DELETE STRATEGY
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function delete(Request $request, $strategy_id)
    {
        //
        try {
            $api_pair = Strategy::findOrFail($strategy_id);
            $api_pair->delete();

            return Redirect::back()->with('success', 'User updated.');

        } catch (\Exception $ex) {
            return Response::json([
                'message' => $ex->getMessage()
            ], 400);
        }
    }


}
