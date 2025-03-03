<?php

namespace App\Modules\Master\Base;

use App\Enums\Esm2SubSys;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\Log;

class GetDeliveryTimeHopeMap implements GetDeliveryTimeHopeMapInterface
{
    /**
     * ESM2.0 APIマネージャー
     */
    protected $esm2ApiManager;

    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    /**
     * API接続先
     */
    protected $connectionApiUrl = 'searchDeliveryTimeHopeMap';

    public function __construct(Esm2ApiManager $esm2ApiManager, EsmSessionManager $esmSessionManager)
    {
        $this->esm2ApiManager = $esm2ApiManager;
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute()
    {
        // 取得処理
        $extendData = [
            'm_account_id' => $this->esmSessionManager->getAccountId(),
            'operator_id' => $this->esmSessionManager->getOperatorId(),
            'feature_id' => 'master/operators/list',
            'display_csv_flag' => 0,
        ];

        $requestData =  [
            'delete_flg' => '0',
        ];

        $searchResult = $this->esm2ApiManager->executeSearchApi($this->connectionApiUrl, Esm2SubSys::MASTER, $requestData, $extendData);

        return $this->formatResult($searchResult);
    }

    /**
     * APIで取得したデータの整形処理
     * @param array $searchResult
     */
    private function formatResult($searchResult)
    {
        $valueArray = [];
        foreach($searchResult['search_result'] as $resRow) {
            // TODO
            $valueArray[] = [
                'm_delivery_time_hope_id' => $resRow['m_delivery_time_hope_map_id'],
                //'delivery_company_cd' => $resRow['delivery_company_cd'],
                //'delivery_company_time_hope_name' => $resRow['delivery_company_time_hope_name'],
            ];
        }
        return $valueArray;
    }

}
