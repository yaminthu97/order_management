<?php

namespace App\Modules\Order\Gfh1207;

use App\Modules\Order\Base\CallEcbeingApiInterface;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * Call Ecbeing Api
 */
class CallEcbeingApi implements CallEcbeingApiInterface
{
    //throw error code constants
    private const PRIVATE_THROW_ERR_CODE = -1;

    /**
     * Execute Call Ecbeing Api
     * @param string $key
     * @param Client $client
     * @param ApiNameListEnum $apiName
     * @param string $securityValue
     * @return mixed
     * @throws Exception
     */
    public function execute($key, $client, $apiName, $securityValue)
    {

        try {

            //get api name lable
            $apiNameLabel  = $apiName->label();

            $response = $client->request('POST', $apiName->value, [
                'form_params' => [
                    'key' => $key,
                    'security' => $securityValue
                ],
            ]);

            //get status code
            $responseStatusCode = $response->getStatusCode();

            // Check if the response status code is between 300 and 308
            if ($responseStatusCode >= 300 && $responseStatusCode <= 308) {

                // Error message to laravel.log
                Log::error('response message : ' . $response->getBody()->getContents());

                //Ecbeingとの通信に失敗しました。 API名: apiName ステータスコード: status code
                throw new Exception(
                    __('messages.error.api_error_with_status_code', [
                        'APIname' => $apiNameLabel,
                        'APIstatus' => $responseStatusCode
                    ]),
                    self::PRIVATE_THROW_ERR_CODE
                );
            }

            return $response;
        } catch (RequestException $e) { // ステータスコードエラー（404や500など）の処理

            // Error message to laravel.log
            Log::error('response message : ' . $e->getMessage());

            // process:apiName APIコールに失敗しました。message save to 'execute_result'
            throw new Exception(__('messages.error.process_failed', ['process' => $apiNameLabel . 'APIコール']), self::PRIVATE_THROW_ERR_CODE);
        } catch (ConnectException $e) { // 接続エラー（ネットワークエラーなど）の処理
            // Error message to laravel.log
            Log::error('response message : ' . $e->getMessage());

            // process:apiName APIコールに失敗しました。message save to 'execute_result'
            throw new Exception(__('messages.error.process_failed', ['process' => $apiNameLabel . 'APIコール']), self::PRIVATE_THROW_ERR_CODE);
        }
    }
}
