<?php

namespace App\Clients\Http;

use App\Enums\Esm2SubSys;
use App\Events\Esm2ApiConnectionFailed;
use App\Events\Esm2ApiRequestSending;
use App\Events\Esm2ApiResponseReceived;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class Esm2ApiClient
{
    protected $version = 'v1_0';

    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function request(string $method, $uri = '', array $options = [])
    {
        try{
            // リクエスト送信前のイベントを発火
            Esm2ApiRequestSending::dispatch($method, $uri, $options);
            $response = $this->client->request($method, $uri, $options);
            $body = json_decode($response->getBody(), true);
            // レスポンスのステータスコードが200系でなければ、受信失敗のイベントを発火
            if($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {

                if(isset($body['response']['result']['error']['code']) && $body['response']['result']['error']['code'] ==='') {
                    Esm2ApiResponseReceived::dispatch($response, $uri);
                }else{
                    // ステータスコードが200系でも、レスポンス構造が不正な場合、エラーコードがある場合は受信失敗のイベントを発火
                    Esm2ApiConnectionFailed::dispatch($response, $uri, null);
                }
            }else{
                // レスポンス受信成功後のイベントを発火
                Esm2ApiConnectionFailed::dispatch($response, $uri, null);
            }
            return $body['response'];
        }catch(\Throwable $e) {
            // レスポンス受信失敗後のイベントを発火
            Esm2ApiConnectionFailed::dispatch(null, $uri, $e);
            throw $e;
        }
    }

    public function buildBaseUrl(Esm2SubSys $subSys)
    {
        return $this->getDomain(). $subSys->getSubSysForUrl(). "/". $this->version;
    }

    private function getDomain()
    {
        $apiDomain = Config::get('Common.const.API_DOMAIN');

        // $apiDomain の末尾にスラッシュが無ければ追加
        if (substr($apiDomain, -1) !== '/') {
            $apiDomain .= '/';
        }
        return $apiDomain;
    }


    /**
     * プロキシの設定をする
     */
    // protected function _setProxy()
    // {
    //     $proxyAddress = '';

    //     $proxyFlag = env('USE_PROXY', '0');

    //     if($proxyFlag) {
    //         $proxyAddress = env('HTTP_PROXY');

    //         $noProxyData = explode(',', env('NO_PROXY_SERVER'));

    //         foreach($noProxyData as $noProxyHost) {
    //             if(strpos($this->_baseUri, $noProxyHost) !== false) {
    //                 $proxyAddress = '';
    //                 break;
    //             }
    //         }
    //     }

    //     return $proxyAddress;
    // }

}
