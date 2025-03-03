<?php

namespace App\Modules\Order\Gfh1207;

use App\Models\Master\Base\ShopGfhModel;
use App\Models\Order\Base\OrderHdrModel;
use App\Modules\Order\Base\SendEcbeingNyukinOrderDataInterface;
use App\Modules\Order\Gfh1207\Enums\ApiNameListEnum;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendEcbeingNyukinOrderData implements SendEcbeingNyukinOrderDataInterface
{
    private const PRIVATE_THROW_ERR_CODE = -1;// const variable for exception error
    private const API_SUCCESS_RESPONSE = 0;// const variable for api response error
    private const EC_ORDER_SYNC_FLAG = 1;
    private const WEB_ORDER_NUM_INDEX = 1;
    private const HTTP_300_SERIES_STATUS_CODE = 300;// const variable for api Http status code

    /**
     * SendEcbeingShipData' s execute
     *
     * [個別処理]入金・受注修正データ
     * 下記のEcbeingAPIを利用して、入金・受注修正データの送信を行う。
     * - 入金・受注修正データ取込API
     * - 入金・受注変更データ更新API
     * @param array $argument
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

        // encrypt with md5 (key.ecbeing API 特定文字列_入金・受注変更データ取込)
        $getSecurityValue = app(GetSecurityValue::class);

        // Get import file
        $nyukinInputFile = $argument['nyukinInputFile'];

        $importSecurity = $getSecurityValue->execute($key, $getApiData->ecbeing_api_imp_nyukin);

        $client = new Client([
            'base_uri' =>  $getApiData->ecbeing_api_base_url,// ecbeing APIベースURL
        ]);

        // 出荷確定データ取込 API Call
        try {
            // write the log info in laravel.log for 出荷確定データ取込API
            Log::info("url : " . $getApiData->ecbeing_api_base_url . ApiNameListEnum::IMP_NYUKIN->value);
            Log::info("key : " . $key);
            Log::info("security : " . $importSecurity);
            Log::info("filename : " . $nyukinInputFile);

            //get tsv file contents
            $fileContents = Storage::disk($argument['disk'])->get($nyukinInputFile);

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

            // 入金・受注変更データ取込
            $responseImport = $client->request('POST', ApiNameListEnum::IMP_NYUKIN->value, [
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

            // repsonse from 入金・受注変更データ取込
            $resultImport = $responseImport->getBody()->getContents();

            // 300 series status code is not error code so check in try case
            if ($responseImport->getStatusCode() >= self::HTTP_300_SERIES_STATUS_CODE) {

                // 出荷確定データ取込 API response log in laravel.log
                logger('response message : ' . $resultImport);

                // if API response is 300 series, save 'Ecbeingとの通信に失敗しました。 API名: 入金・受注変更データ取込、 ステータスコード: {$responseImport->getStatusCode()}'  to execute_result
                throw new Exception(__('messages.error.api_error_with_status_code', ['APIname' => ApiNameListEnum::IMP_NYUKIN->label(), 'APIstatus' => $responseImport->getStatusCode()]), self::PRIVATE_THROW_ERR_CODE);
            }

            // show response log in console for 出荷確定データ取込 API
            Log::info("response : " . $resultImport);

            // Check the 入金・受注変更データ取込 API response
            if ($resultImport != self::API_SUCCESS_RESPONSE) {

                // if API response is failed, save 'Ecbeing APIからエラーが戻されました。API名:入金・受注変更データ取込、 レスポンスコード:{$resultImport}' to execute_result
                throw new Exception(__('messages.error.api_error_with_response_code', ['APIname' => ApiNameListEnum::IMP_NYUKIN->label(), 'APIresponse' => $resultImport]), self::PRIVATE_THROW_ERR_CODE);
            }
        } catch (ConnectException $e) { // 接続エラー（ネットワークエラーなど）の処理

            // Write the error log in laravel.log
            logger('response message : ' . $e->getMessage());

            // directly from the API Call
            // 出荷確定データ取込APIコールに失敗しました。message save to 'execute_result'
            throw new Exception(__('messages.error.process_failed', ['process' => ApiNameListEnum::IMP_NYUKIN->label() . 'APIコール']), self::PRIVATE_THROW_ERR_CODE);

        } catch (RequestException $e) { // ステータスコードエラー（404や500など）の処理

            // Write the error log in laravel.log
            logger('response message : ' . $e->getMessage());

            // directly from the API
            // '出荷確定データ取込APIコールに失敗しました' message save to 'execute_result'
            throw new Exception(__('messages.error.process_failed', ['process' => ApiNameListEnum::IMP_NYUKIN->label() . 'APIコール']), self::PRIVATE_THROW_ERR_CODE);
        }

        // 入金・受注変更データ更新 API Call
        // encrypt with md5 (key.ecbeing API 特定文字列_入金・受注変更データ更新)
        $updateSecurity = $getSecurityValue->execute($key, $getApiData->ecbeing_api_update_nyukin);

        // write the log info in laravel.log for 出荷確定データ更新 API
        Log::info("url : " . $getApiData->ecbeing_api_base_url . ApiNameListEnum::UPDATE_NYUKIN->value);
        Log::info("key : " . $key);
        Log::info("security : " . $updateSecurity);

        // call the ecbeingApiCall module to call API
        $ecbeingApiCall = app(CallEcbeingApi::class);

        // 入金・受注変更データ更新 API Call
        $updateReponse = $ecbeingApiCall->execute($key, $client, ApiNameListEnum::UPDATE_NYUKIN, $updateSecurity);

        // response from 入金・受注変更データ更新
        $resultUpdate = $updateReponse->getBody()->getContents();

        // show response log in console for 入金・受注変更データ更新 API
        Log::info("response : " . $resultUpdate);

        // Check the 入金・受注変更データ更新 API response
        if ($resultUpdate != self::API_SUCCESS_RESPONSE) {
            // if API response is failed, save 'Ecbeing APIからエラーが戻されました。API名:入金・受注変更データ更新 レスポンスコード:{$resultUpdate}' to execute_result
            throw new Exception(__('messages.error.api_error_with_response_code', ['APIname' => ApiNameListEnum::UPDATE_NYUKIN->label(), 'APIresponse' => $resultUpdate]), self::PRIVATE_THROW_ERR_CODE);
        }

        $webOrderNumbers = [];
        // 入金・受注変更データ.WEB受注NO.
        // Read the content of the file from S3
        $lines = Storage::disk('s3')->get($nyukinInputFile);

        // 文字コードをチェックし、UTF-8に変換する
        $encoding = mb_detect_encoding($lines, ['SJIS-win', 'SJIS-WIN', 'SJIS', 'UTF-8', 'EUC-JP'], true);

        if ($encoding) {
            // SJIS系の表記揺れを統一（SJIS-win / SJIS-WIN → SJIS）
            if (stripos($encoding, 'SJIS') !== false) {
                $encoding = 'SJIS'; // 統一する
            }

            // UTF-8 に変換
            $lines = mb_convert_encoding($lines, 'UTF-8', $encoding);
        }

        // tsv to array based on newlines
        $tsvArr = explode("\n", $lines);

        // Skip the header and loop each line
        $webIndex = self::WEB_ORDER_NUM_INDEX;
        foreach ($tsvArr as $key => $line) {
            if ($key < $webIndex) {
                continue; // Filtering the Header column
            }

            $columns = explode("\t", $line); // Tab-separated array

            // WEB受注 NO. column is at Index 1
            if (isset($columns[$webIndex])) {
                $webOrderNumbers[] = $columns[$webIndex];
            }
        }

        // 受注基本のWeb受注変更連携フラグ、Web受注変更連携日時を更新する。
        if (!empty($webOrderNumbers)) {
            OrderHdrModel::whereIn('ec_order_num', $webOrderNumbers)
            ->update([
                'ec_order_sync_flg' => self::EC_ORDER_SYNC_FLAG,
                'ec_order_sync_datetime' => $argument['batchStartDateAndTime']
            ]);
        }
    }
}
