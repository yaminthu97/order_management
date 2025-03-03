<?php

namespace App\Modules\Common;

use App\Modules\Common\CommonModule;
use Carbon\Carbon;
use Config;

/**
 * 共通機能／TOP.
 *
 * Class TopManager
 */
class TopManager extends CommonModule
{
    private $_config;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        parent::__construct();
        $this->_config = Config::get('Common.const.TopManager');
    }

    /**
     * バッチ実行履歴を取得する.
     *
     *   設定ファイルから取得した日数分さかのぼって検索した結果＋さらにさかのぼって30件
     *
     * @param null $accountId
     * @param null $operatorId
     *
     * @return array|void
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function searchBatchInstruction($accountId = null, $operatorId = null)
    {
        $ids = $this->getRequiredId($accountId, $operatorId);
        if (count($ids) === 0) {
            return [];
        }

        // 1．システム日付から日数分さかのぼった日付分のバッチ実行履歴を取得する
        $subDays = (int) $this->_config['BatchExecInstructionDays'] + 1;
        $targetDate = Carbon::now($this->timeZone)->subDay($subDays);

        $search_info = [
            'm_operator_id' => $ids['OperatorID'],
            'execute_status' => 1,
            'batchjob_start_datetime_value_more' => $targetDate->format('Y-m-d H:i:s'),
            'orderby' => 'batchjob_start_datetime',
            'desc' => 1,
            'limit' => 200,
        ];

        $reqParam = [
            'request' => [
                'm_account_id' => $ids['AccountID'],
                'operator_id' => $ids['OperatorID'],
                'feature_id' => str_replace(Config::get('Common.const.APP_URL'), '', url()->current()),
                'display_csv_flag' => 0,
                'search_info' => $search_info,
            ],
        ];

        $response = $this->callApi('POST', 'common/'.$this->getVer().'/searchExecuteBatchInstruction', $reqParam, 'common');
        $body = json_decode($response->getBody(), true);
        if (!empty($body['response']['error']['code'])) {
            dd($body);
        }

        $firstResult = $body['response']['search_result'];
        unset($search_info['batchjob_start_datetime_value_more']);

        // 2．１番から更にさかのぼって30件分取得する
        $search_info['batchjob_start_datetime_value_less'] = $targetDate->format('Y-m-d H:i:s');
        $search_info['limit'] = 30;

        $reqParam['request']['search_info'] = $search_info;

        $response = $this->callApi('POST', 'common/'.$this->getVer().'/searchExecuteBatchInstruction', $reqParam, 'common');
        $body = json_decode($response->getBody(), true);
        if (!empty($body['response']['error']['code'])) {
            dd($body);
        }
        $secondResult = $body['response']['search_result'];

        return array_merge($firstResult, $secondResult);
    }
}
