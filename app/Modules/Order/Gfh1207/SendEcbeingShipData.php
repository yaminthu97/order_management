<?php

namespace App\Modules\Order\Gfh1207;

use App\Models\Master\Base\ShopGfhModel;
use App\Modules\Order\Base\SendEcbeingShipDataInterface;
use App\Modules\Order\Gfh1207\Enums\ApiNameListEnum;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendEcbeingShipData implements SendEcbeingShipDataInterface
{
    private const PRIVATE_THROW_ERR_CODE = -1;// const variable for exception error
    private const API_SUCCESS_RESPONSE = 0;// const variable for api response error

    private const HTTP_300_SERIES_STATUS_CODE = 300;// const variable for api Http status code

    /**
     * SendEcbeingShipData' s execute
     *
     * [個別処理]出荷確定データ送信処理
     * 下記のEcbeingAPIを利用して、出荷確定データの送信を行う。
     * - 出荷確定データ取込API
     * - 出荷確定データ更新API
     *
     */
    public function execute($argument)
    {
        // current datetime
        $key = Carbon::now()->format('YmdHis');

        // get api data
        $getApiData = ShopGfhModel::orderBy('m_shop_gfh_id', 'desc')->first();

        // [APIの基本情報が取得できませんでした。] message save to 'execute_result'
        if (empty($getApiData)) {
            throw new Exception(__('messages.error.batch_error.data_not_found3', ['data' => 'APIの基本']), self::PRIVATE_THROW_ERR_CODE);
        }

        // call the getSecurityValue module to generate code
        $getSecurityValue = app(GetSecurityValue::class);

        // Get import file
        $shipInputFile = $argument['shipInputFile'];

        $client = new Client([
            'base_uri' =>  $getApiData->ecbeing_api_base_url,// ecbeing APIベースURL
        ]);

        // 出荷確定データ取込 API Call
        $this->impShipConfirm($client, $getApiData, $key, $shipInputFile, $getSecurityValue, $argument['disk']);

        // 出荷確定データ更新 API Call
        $this->updateShipConfirm($client, $getApiData, $key, $getSecurityValue);
    }

    /**
     * 出荷確定データ取込 API Call
     *
     * This method call ecbeing' s 出荷確定データ取込 api
     * The given client GuzzleHttp\Client, getApiData array, key date, shipInputFile string, getSecurityValue app.
     *
     *Call API and if something wrong, goto catch(ConnectionException/ RequsetException) case
     * ConnectionException 接続エラー（ネットワークエラーなど）の処理
     * RequsetException ステータスコードエラー（404や500など）の処理
     * その他の予期しないエラー is goto handle function' s catch case
     *
     * @param  $client, $getApiData, $key, $shipInputFile, $getSecurityValue  to call 出荷確定データ取込 API from ecbeing
     *
     */
    private function impShipConfirm($client, $getApiData, $key, $shipInputFile, $getSecurityValue, $s3Disk)
    {

        try {

            // encrypt with md5 (key.ecbeing API 特定文字列_出荷確定データ取込)
            $importSecurity = $getSecurityValue->execute($key, $getApiData->ecbeing_api_imp_ship);

            // write the log info in laravel.log for 出荷確定データ取込API
            Log::info("url : " . $getApiData->ecbeing_api_base_url . ApiNameListEnum::IMP_SHIP->value);
            Log::info("key : " . $key);
            Log::info("security : " . $importSecurity);
            Log::info("filename : " . $shipInputFile);

            //get tsv file contents
            $fileContents = Storage::disk($s3Disk)->get($shipInputFile);

            // 文字コードをチェックし、UTF-8に変換する
            $encoding = mb_detect_encoding($fileContents, ['SJIS-win', 'SJIS-WIN', 'SJIS', 'UTF-8', 'EUC-JP'], true);
            if ($encoding) {
                // SJIS系の表記揺れを統一（SJIS-win / SJIS-WIN → SJIS）
                if (stripos($encoding, 'SJIS') !== false) {
                    $encoding = 'SJIS'; // 統一する
                }

                // UTF-8 に変換
                $fileContents = mb_convert_encoding($fileContents, 'UTF-8', $encoding);
            }

            // 出荷確定データ取込API
            $responseImport = $client->request('POST', ApiNameListEnum::IMP_SHIP->value, [
                'multipart' => [
                    [
                        'name'     => 'filename',
                        'contents' => $fileContents,
                    ],
                    [
                        'name'     => 'key',
                        'contents' => $key,
                    ],
                    [
                        'name'     => 'security',
                        'contents' => $importSecurity,
                    ],
                ],
            ]);
            // repsonse from 出荷確定データ取込
            $resultImport = $responseImport->getBody()->getContents();

            // 300 series status code is not error code so check in try case
            if ($responseImport->getStatusCode() >= self::HTTP_300_SERIES_STATUS_CODE) {

                // 出荷確定データ取込 API response log in laravel.log
                logger('response message : ' . $resultImport);

                // if API response is 300 series, save 'Ecbeingとの通信に失敗しました。 API名: 出荷確定データ取込、 ステータスコード: {$responseImport->getStatusCode()}'  to execute_result
                throw new Exception(__('messages.error.api_error_with_status_code', ['APIname' => ApiNameListEnum::IMP_SHIP->label(), 'APIstatus' => $responseImport->getStatusCode()]), self::PRIVATE_THROW_ERR_CODE);
            }

            // show response log in console for 出荷確定データ取込 API
            Log::info("response : " . $resultImport);

            // Check the 出荷確定データ取込 API response
            if ($resultImport != self::API_SUCCESS_RESPONSE) {

                // if API response is failed, save 'Ecbeing APIからエラーが戻されました。API名:出荷確定データ取込、 レスポンスコード:{$resultImport}' to execute_result
                throw new Exception(__('messages.error.api_error_with_response_code', ['APIname' => ApiNameListEnum::IMP_SHIP->label(), 'APIresponse' => $resultImport]), self::PRIVATE_THROW_ERR_CODE);
            }
        } catch (ConnectException $e) { // 接続エラー（ネットワークエラーなど）の処理

            // Write the error log in laravel.log
            logger('response message : ' . $e->getMessage());

            // directly from the API Call
            // 出荷確定データ取込APIコールに失敗しました。message save to 'execute_result'
            throw new Exception(__('messages.error.process_failed', ['process' => ApiNameListEnum::IMP_SHIP->label() . 'APIコール']), self::PRIVATE_THROW_ERR_CODE);

        } catch (RequestException $e) { // ステータスコードエラー（404や500など）の処理

            // Write the error log in laravel.log
            logger('response message : ' . $e->getMessage());

            // directly from the API
            // '出荷確定データ取込APIコールに失敗しました' message save to 'execute_result'
            throw new Exception(__('messages.error.process_failed', ['process' => ApiNameListEnum::IMP_SHIP->label() . 'APIコール']), self::PRIVATE_THROW_ERR_CODE);

        }
    }

    /**
     * 出荷確定データ更新 API Call
     *
     * This method call ecbeing' s 出荷確定データ更新 api
     * The given client GuzzleHttp\Client, getApiData array, key date, shipInputFile string, getSecurityValue app.
     *
     * @param  $client, $getApiData, $key, $shipInputFile, $getSecurityValue  to call 出荷確定データ更新 API from ecbeing
     *
     */
    private function updateShipConfirm($client, $getApiData, $key, $getSecurityValue)
    {

        // encrypt with md5 (key.ecbeing API 特定文字列_出荷確定データ更新)
        $updateSecurity = $getSecurityValue->execute($key, $getApiData->ecbeing_api_update_ship);

        // write the log info in laravel.log for 出荷確定データ更新 API
        Log::info("url : " . $getApiData->ecbeing_api_base_url . ApiNameListEnum::UPDATE_SHIP->value);
        Log::info("key : " . $key);
        Log::info("security : " . $updateSecurity);

        // call the ecbeingApiCall module to call API
        $ecbeingApiCall = app(CallEcbeingApi::class);
        // 出荷確定データ更新API call
        $updateReponse = $ecbeingApiCall->execute($key, $client, ApiNameListEnum::UPDATE_SHIP, $updateSecurity);

        // repsonse from 出荷確定データ更新
        $resultUpdate = $updateReponse->getBody()->getContents();

        // show response log in console for 出荷確定データ更新 API
        Log::info("response : " . $resultUpdate);

        // Check the 出荷確定データ更新 API response
        if ($resultUpdate != self::API_SUCCESS_RESPONSE) {
            // if API response is failed, save 'Ecbeing APIからエラーが戻されました。API名:出荷確定データ更新 レスポンスコード:{$resultUpdate}' to execute_result
            throw new Exception(__('messages.error.api_error_with_response_code', ['APIname' => ApiNameListEnum::UPDATE_SHIP->label(), 'APIresponse' => $resultUpdate]), self::PRIVATE_THROW_ERR_CODE);
        }
    }
}
