<?php

namespace App\Http\Controllers;

use App\Console\Commands\AutoLoader;
use App\Console\Commands\OperatorThread;
use App\Console\Commands\OperatorThreaded;
use App\Http\Exchanges\Binance;
use App\Jobs\ProcessRequest;
use App\Models\ApiPair;
use App\Models\Strategy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Illuminate\Support\Facades\Response;
use Pool;

class ApiPairController extends Controller
{

    /**
     * Update API PAIRS
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(Request $request, $user_id)
    {
        //
        try {
            $user = User::findOrFail($user_id);
            $apiPairsFromRequest = (array)json_decode($request->getContent());
            foreach($apiPairsFromRequest['pairs'] as $pair) {
                $pair = (array) $pair;
                unset($pair['ind']);
                $pair['user_id'] = \auth()->id();

                if(isset($pair['id']))
                {
                    ApiPair::updateOrCreate(
                        ['id' => $pair['id']],
                        $pair
                    );
                }
                else
                {
                    ApiPair::updateOrCreate(
                        ['user_id' => $pair['user_id'], 'api_key' => $pair['api_key'], 'api_secret' => $pair['api_secret']],
                        $pair
                    );
                }
            }

            return Redirect::back()->with('success', 'User updated.');


//            return Inertia::render('Users/Edit', [
//                'user' => [
//                    'id' => $user->id,
//                    'first_name' => $user->first_name,
//                    'last_name' => $user->last_name,
//                    'email' => $user->email,
//                    'owner' => $user->owner,
//                    'api_pairs' => $user->apipairs,
//                    'webhookurl' => route('webhook', ['code' => $user->code]),
//                    'photo' => $user->photo_path ? URL::route('image', ['path' => $user->photo_path, 'w' => 60, 'h' => 60, 'fit' => 'crop']) : null,
//                    'deleted_at' => $user->deleted_at,
//                ],
//            ]);
        } catch (\Exception $ex) {
            return Response::json([
                'message' => $ex->getMessage()
            ], 400);
        }
    }

    /**
     * DELETE API PAIR
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function delete(Request $request, $pair_id)
    {
        //
        try {
            $user = User::findOrFail(\auth()->id());

            $api_pair = ApiPair::findOrFail($pair_id);
            if($api_pair->user_id = $user->id)
                $api_pair->delete();

            return Redirect::back()->with('success', 'User updated.');

        } catch (\Exception $ex) {
            return Response::json([
                'message' => $ex->getMessage()
            ], 400);
        }
    }

    /**
     * Handle Webhook responses
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function webhook(Request $request, $code)
    {
        //
        try {
            if (!$request->hasValidSignature()) {
                abort(401);
            }
            $user = User::where('code', '=', $code)->firstOrFail();
            $data = (array)json_decode($request->getContent());

            $strategy = Strategy::where('code', '=', $data['strategy_code'])->firstOrFail();

//            $operators = [];
            foreach ($strategy->apipairs as $apipair)
            {
//                $operators[] = new OperatorThreaded($apipair->api_key, $apipair->api_secret, $user->id, strtoupper($data['action']), $data['stable_coin'], $data['crypto_asset']);
                ProcessRequest::dispatch($apipair->api_key, $apipair->api_secret, $user->id, strtoupper($data['action']), $data['stable_coin'], $data['crypto_asset']);
            }
//
//            $pool = new Pool(count($operators), Autoloader::class);
//            foreach ($operators as $operator) {
//                $thread = new OperatorThread($operator);
//                $pool->submit($thread);
//            }
//
//            while ($pool->collect());
//            $pool->shutdown();

            return Response::json([
                'message' => 'Success'
            ], 200);
        } catch (\Exception $ex) {
            return Response::json([
                'message' => $ex->getMessage()
            ], 400);
        }
    }


}
