<?php

namespace App\Services;

use App\Clients\Http\Esm2ApiClient;
use App\Enums\Esm2SubSys;
use Illuminate\Support\Facades\Log;

/**
 * ESM2.0のAPIサーバーとの通信を行うクラス
 */
class Esm2ApiManager
{
    /**
     * ESM2.0 APIクライアント
     */
    protected $client;

    /**
     * ログイン情報
     */
    protected $loginSessionInfo = [];

    public function __construct(Esm2ApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * APIで取得したデータを配列で返却（検索用）
     * @param string $connectionApiUrl
     * @param Esm2SubSys $esm2SubSys
     * @param array $where
     * @param array $extendData
     * @return array
     */
    public function executeSearchApi($connectionApiUrl, Esm2SubSys $esm2SubSys, $where = [], $extendData = [])
    {
        Log::debug('START executeSearchApi');
        $requestData =  [
            'search_info' => $where,
        ];

        foreach($extendData as $key => $row) {
            $requestData[$key] = $row;
        }

        return $this->connectionApi(['request' => $requestData], $connectionApiUrl, $esm2SubSys);
    }

    public function executeRegisterApi($connectionApiUrl, Esm2SubSys $esm2SubSys, $data = [], $extendData = [])
    {
        Log::debug('START executeRegisterApi');
        $requestData =  [
            'register_info' => $data,
        ];

        foreach($extendData as $key => $row) {
            $requestData[$key] = $row;
        }

        return $this->connectionApi(['request' => $requestData], $connectionApiUrl, $esm2SubSys);
    }

    /**
     * APIの実行
     * @param array $requestData
     * @param string $connectionUrl
     * @param Esm2SubSys $subsystem デフォルトはGLOBAL
     */
    public function connectionApi($requestData, $connectionUrl, Esm2SubSys $subsystem = Esm2SubSys::GLOBAL)
    {
        // 共通及びマスタの場合は全数取得するようにする
        if($subsystem === Esm2SubSys::MASTER || $subsystem === Esm2SubSys::GLOBAL) {
            if(isset($requestData['request']['search_info'])) {
                $requestData['request']['search_info']['search_use_type'] = 1;
            }
        }

        $res =  [
            'json' => $requestData,
            'http_errors' => true,
            'headers' => ['Content-Type' => 'application/json'],
        ];

        $requestUrl = $this->client->buildBaseUrl($subsystem) . '/'. $connectionUrl;

        return $this->client->request('POST', $requestUrl, $res);
    }

}
