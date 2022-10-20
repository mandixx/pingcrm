<?php


namespace App\Console\Commands;

use App\Http\Exchanges\Binance;
use App\Models\ApiPair;
use App\Models\User;

class OperatorThreaded
{
    public String $action;

    private $user_id;
    private $api_key;
    private $api_secret;
    private $crypto_asset;
    private $stable_coin;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($apiKeyParam, $apiSecretParam, $userIdParam, $action, $stableCoinParam, $cryptoAssetParam)
    {
        $this->api_key = $apiKeyParam;
        $this->api_secret = $apiSecretParam;
        $this->user_id = $userIdParam;
        $this->action = $action;
        $this->stable_coin = $stableCoinParam;
        $this->crypto_asset = $cryptoAssetParam;
    }

    public function handle()
    {
        if (app()->isBooted()) {
            $user = User::findOrFail($this->user_id);
            $api_pair = ApiPair::where('api_key', '=', $this->api_key)->where('api_secret', '=', $this->api_secret)->firstOrFail();
            $exchange = new Binance($user, $api_pair, $this->crypto_asset, $this->stable_coin);

            if(strtoupper($this->action === Binance::SIDE_BUY))
                $exchange->open_spot_position();
            else if(strtoupper($this->action === Binance::SIDE_SELL))
                $exchange->close_spot_position();

            echo 'TEST';
        }
    }

}
