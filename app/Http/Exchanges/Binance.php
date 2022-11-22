<?php


namespace App\Http\Exchanges;


use App\Http\Exchanges\Clients\BinanceClient;
use App\Models\ApiPair;
use App\Models\CustomTrade;
use App\Models\User;
use Binance\Spot;
use Illuminate\Support\Facades\Log;

class Binance
{

    private Spot $client;
    private ApiPair $pair;
    private User $user;

    public const SIDE_BUY = 'BUY';
    public const SIDE_SELL = 'SELL';

    private String $asset;
    private String $stable_coin;

    // Max attempts to repeat each request if it fails
    private int $max_attempts = 3;

    public function __construct($userParam, $apiPair, $assetParam, $stableCoinParam)
    {

        $this->user = $userParam;
        $this->pair = $apiPair;

        $this->client = new Spot(['key' => $this->pair->api_key, 'secret' => $this->pair->api_secret]);

        $this->asset = $assetParam;
        $this->stable_coin = $stableCoinParam;
    }

    public function open_spot_position($bot_starting_position_percent=0.9, $bot_tp_percent=0.8, $bot_sl_percent=0.2)
    {
        try {
            $current_acc_balance = self::get_account_balances()['stable_coin'];

            $symbol_price = self::get_ticker_price();

            $take_profit_price = self::floorp($symbol_price + $symbol_price * ( $bot_tp_percent / ( 1 * 100 )), 2);
            $stop_loss_price = self::floorp($symbol_price - $symbol_price * ( $bot_sl_percent / ( 1 * 100 )), 2);

            $available_bot_balance_for_trade = self::floorp($current_acc_balance * $bot_starting_position_percent,2);

            $bot_quantity = self::floorp($available_bot_balance_for_trade / $symbol_price, 4);

            $open_position_response = self::spot_buy_asset($bot_quantity);

            $open_tp_sl_position_response = self::spot_send_take_profit_order($take_profit_price, $stop_loss_price, $bot_quantity);

            self::afterSuccessfulOrder($open_position_response, $open_tp_sl_position_response);

        } catch (\Exception $ex) {
            // Handle exception
            self::tradeFailed();
        }
    }

    public function close_spot_position()
    {

        // Cancel all orders
        try {

            $open_oco_orders_response = $this->client->getOpenOcoOrders();
            if(!empty($open_oco_orders_response))
                $cancel_open_orders_response = $this->client->cancelOpenOrders($this->asset . $this->stable_coin);


            $current_acc_balance = self::get_account_balances()['asset'];

            $symbol_price = self::get_ticker_price();

            $bot_quantity = self::floorp($current_acc_balance, 4);

            $close_position_response = self::spot_sell_asset($bot_quantity);

            self::afterSuccessfulOrder($close_position_response);

        } catch (\Exception $ex) {

        }
    }

    public function spot_buy_asset($quantity)
    {
        $exc = null;
        $attempts = 0;
        while ($attempts < $this->max_attempts)
        {
            try {
                    $symbol = $this->asset . $this->stable_coin;
                    return $this->client->newOrder($symbol,
                        'BUY',
                        'MARKET',
                        [
                            'quantity' => $quantity,
                        ]);
            } catch (\Exception $exception)
            {
                $exc = $exception;
                $attempts++;
            }
        }

        if($attempts === $this->max_attempts) {
            throw $exc;
        }
    }

    public function spot_sell_asset($quantity)
    {
        $exc = null;
        $attempts = 0;
        while ($attempts < $this->max_attempts)
        {
            try {
                $symbol = $this->asset . $this->stable_coin;
                return $this->client->newOrder($symbol,
                    'SELL',
                    'MARKET',
                    [
                        'quantity' => $quantity,
                    ]);
            } catch (\Exception $exception)
            {
                $exc = $exception;
                $attempts++;
            }
        }

        if($attempts === $this->max_attempts) {
            throw $exc;
        }
    }

    public function spot_send_take_profit_order($take_profit, $stop_loss, $quantity)
    {
        $exc = null;
        $attempts = 0;
        while ($attempts < $this->max_attempts)
        {
            try {
                $symbol = $this->asset . $this->stable_coin;
                return $this->client->newOcoOrder($symbol,
                    'SELL',
                    $quantity,
                    $take_profit,
                    $stop_loss,
                    [
                        'listClientOrderId' => 'sltporder',
                        'stopLimitPrice' => $stop_loss - 50,
                        'stopLimitTimeInForce' => 'FOK'
                    ]
                );

            } catch (\Exception $exception)
            {
                $exc = $exception;
                $attempts++;
            }
        }

        if($attempts === $this->max_attempts) {
            throw $exc;
        }
    }

    /**
     * Get account balances based on what pair is traded
     * @returns array with balances
     */
    public function get_account_balances()
    {
        $exc = null;
        $attempts = 0;
        while ($attempts < $this->max_attempts)
        {
            try {
                $response = $this->client->account();
                return self::filter_account_balances($response['balances']);
            } catch (\Exception $exception)
            {
                $exc = $exception;
                $attempts++;
            }
        }

        if($attempts === $this->max_attempts) {
            throw $exc;
        }

    }

    /**
     * Get spot trades
     * @returns array with trades
     */
    public function get_account_trades()
    {
        $exc = null;
        $attempts = 0;
        while ($attempts < $this->max_attempts)
        {
            try {
                $symbol = $this->asset . $this->stable_coin;
                return $this->client->myTrades($symbol, [
                    'limit' => 500
                ]);
            } catch (\Exception $exception)
            {
                $exc = $exception;
                $attempts++;
            }
        }

        if($attempts === $this->max_attempts) {
            throw $exc;
        }

    }

    /**
     * Returns the symbol price
     */
    public function get_ticker_price()
    {
        $exc = null;
        $attempts = 0;
        while ($attempts < $this->max_attempts)
        {
            try {
                $response = $this->client->tickerPrice([
                    'symbol' => $this->asset . $this->stable_coin
                ]);

                return $response['price'];
            }
            catch (\Exception $exception) {
                $exc = $exception;
                $attempts++;
            }
        }

        if($attempts === $this->max_attempts) {
            throw $exc;
        }
    }

    /**
     * Filters the account balances - returns CRYPTO balance and STABLE COIN balance
     * @param $balances
     * @return array
     */
    private function filter_account_balances($balances)
    {
        $filtered_balances = [];
        foreach ($balances as $balance)
        {
            if($balance['asset'] === $this->asset)
                $filtered_balances['asset'] = $balance['free'];
            if($balance['asset'] === $this->stable_coin)
                $filtered_balances['stable_coin'] = $balance['free'];
        }
        return $filtered_balances;
    }

    /**
     * Run after every successful buy or sell order ( SPOT )
     * @param null $position_response
     * @param null $tp_sl_response
     */
    private function afterSuccessfulOrder($position_response = null, $tp_sl_response = null)
    {
        /**
         * Are we selling or buying ?
         */
        $currentOrder = $tp_sl_response === null ? self::SIDE_SELL : self::SIDE_BUY;
        $data = [];
        if($currentOrder === self::SIDE_BUY)
        {
            $data['user_id'] = $this->user->id;
            $data['api_pair_id'] = $this->pair->id;
            $data['symbol'] = $position_response['symbol'];
            $data['buy_order_id'] = $position_response['orderId'];
            $data['order_list_id'] = $position_response['orderListId'];
            $data['price_buy'] = self::get_average_fill_price($position_response['fills']);
            $data['qty'] = $position_response['executedQty'];
            try {
                CustomTrade::create($data);
                Log::debug(implode($data));
            } catch (\Exception $exception) {
                throw $exception;
            }
        }
        else
        {
            // Get the last trade to update the values
            $latest_trade = CustomTrade::where('user_id', '=', $this->user->id)
                ->where('api_pair_id', '=', $this->pair->id)
                ->where('symbol', '=', $position_response['symbol'])
                ->orderByDesc('created_at')
                ->first();


            $latest_trade->sell_order_id = $position_response['orderId'];
            $latest_trade->order_list_id = $position_response['orderListId'];
            $latest_trade->price_sell = self::get_average_fill_price($position_response['fills']);
            $latest_trade->profit = ($latest_trade->price_sell - $latest_trade->price_buy) * $latest_trade->qty;

            Log::debug('CLOSING TRADE WITH ID - ' . $latest_trade->id);
            Log::debug('PROFIT - ' . $latest_trade->profit);


            try {
                $latest_trade->save();
            } catch (\Exception $exception) {
                throw $exception;
            }
        }
    }

    private function get_average_fill_price($fills = [])
    {
        $counter = 0;
        $priceSum = 0;
        foreach ($fills as $fill)
        {
            $priceSum+= $fill['price'];
            $counter++;
        }

        return self::floorp($priceSum / $counter, 2);
    }

    private function tradeFailed()
    {
        // Cancel all orders
        try {
            $response = $this->client->cancelOpenOrders($this->asset . $this->stable_coin);
        } catch (\Exception $ex) {

        }
    }

    public static function floorp($val, $precision)
    {
        $mult = pow(10, $precision); // Can be cached in lookup table
        return floor($val * $mult) / $mult;
    }
}
