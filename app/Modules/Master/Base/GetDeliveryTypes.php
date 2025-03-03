<?php

namespace App\Modules\Master\Base;

use App\Enums\Esm2SubSys;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\Log;

class GetDeliveryTypes implements GetDeliveryTypesInterface
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
    protected $connectionApiUrl = 'searchDeliveryTypes';

    /**
     * 表示名(フォーマット用)
     */
    protected $displayName = 'm_itemname_type_name';

    /**
     * 値(フォーマット用)
     */
    protected $valueName = 'm_delivery_types_id';

    public function __construct(Esm2ApiManager $esm2ApiManager, EsmSessionManager $esmSessionManager)
    {
        $this->esm2ApiManager = $esm2ApiManager;
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute($key)
    {
        // 取得処理
        $extendData = [
            'm_account_id' => $this->esmSessionManager->getAccountId(),
            'operator_id' => $this->esmSessionManager->getOperatorId(),
            'feature_id' => 'order/order-delivery/info/' . $key,
        ];

        $requestData =  [
            'delete_flg' => '0',
        ];

        $searchResult = $this->esm2ApiManager->executeSearchApi($this->connectionApiUrl, Esm2SubSys::MASTER, $requestData, $extendData);

        return $searchResult['search_result'];
    }

    /**
     * APIで取得したデータの整形処理
     * @param array $searchResult
     */
    private function formatResult($searchResult)
    {
        $valueArray = [];
        foreach($searchResult['search_result'] as $resRow) {
            $valueArray[$resRow[$this->displayName]] = $resRow[$this->valueName];
        }
        return $valueArray;
    }
}
