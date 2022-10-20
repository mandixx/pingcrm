<?php


namespace App\Http\Exchanges\Clients;


use Illuminate\Support\Facades\Config;

class BinanceClient extends Client
{
    /**
     * Constants with endpoints
     */
    public const get_account_information = '/api/v3/account';
    public const get_ticker_price = '/api/v3/ticker/price';
    public const new_order = '/api/v3/order';
    public const test_new_order = '/api/v3/order/test';

    private $apiKey;
    private $apiSecret;
    private $baseUrl;

    public function __construct($apiKeyParam, $apiSecretParam)
    {
       $this->apiKey = $apiKeyParam;
       $this->apiSecret = $apiSecretParam;

       if(Config::get('APP_ENV') == 'production') {
           $this->baseUrl = 'https://api.binance.com';
       }
       else {
           $this->baseUrl = 'https://api.binance.com';
       }
    }

    public function executeCall($api, $method, $data = [], $options = [])
    {
        $url = $this->baseUrl . $api;
        $timestamp = time()*1000; //get current timestamp in milliseconds
        $query_str = [];
        /**
         *
         */
        if(!empty($data))
        {
            $query_str = $data;
        }
        /**
         * If endpoint is signed it requires a signature + timestamp
         */
        if(self::is_endpoint_signed($api))
        {
            $query_str['timestamp'] = $timestamp;
            $query_str = http_build_query($query_str);
            $signature = hash_hmac('sha256', $query_str, $this->apiSecret);
            $query_str .= "&signature=" . $signature;
        }

        //Decide content type
        $content_type = ['Content-Type: application/octet-stream'];
        if($method === self::METHOD_GET)
        {
            // Parameters sent as a query string
        }
        if(in_array($method, [self::METHOD_POST, self::METHOD_PUT, self::METHOD_DELETE]) || self::is_endpoint_signed($api))
        {
            $content_type = ["Content-Type: application/x-www-form-urlencoded","X-MBX-APIKEY: ".$this->apiKey];
        }

        // Final url
        $url .= '?' . $query_str;

        $ch = @curl_init();
        @curl_setopt($ch, CURLOPT_HEADER, false);
        @curl_setopt($ch, CURLOPT_URL, $url);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, $content_type);
        @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        @curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if(!empty($query_str) && $method === self::METHOD_POST)
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $query_str);

        $response = @curl_exec($ch);
        $http_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        @curl_close($ch);

        $json_data = (array)json_decode($response);

        return $this->handleResponse($http_code, $response, $url, $method, $json_data, $api);
    }

    /**
     * @param $http_code
     * @param $response
     * @param $url
     * @param $method
     * @param $json_data
     * @param $api
     * @return array
     */
    private function handleResponse($http_code, $response, $url, $method, $json_data, $api): array
    {
        return [
            'http_code' => $http_code,
            'response' => $response,
            'url' => $url,
            'method' => $method,
            'json_data' => $json_data,
            'api' => $api
        ];
    }


    /**
     * Signed enppints require signature + timestamp attached
     * @param $api - endpoint
     * @return bool
     */
    private function is_endpoint_signed($api)
    {
        return in_array($api, [
            self::get_account_information,
            self::test_new_order,
            self::new_order,
        ]);
    }
}
