<?php

namespace App\Modules\Order\Base;

use App\Enums\Esm2SubSys;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\Log;

class RetrieveDeliveryInfo implements RetrieveDeliveryInfoInterface
{
    /**
     * ESM2.0 APIマネージャー
     */
    protected $esm2ApiManager;

    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;
    public function __construct(Esm2ApiManager $esm2ApiManager, EsmSessionManager $esmSessionManager)
    {
        $this->esm2ApiManager = $esm2ApiManager;
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute($key)
    {
        /**
         * 編集時に取得する際のデータの主キー名称
         */
        $searchPrimaryKey = 't_delivery_hdr_id';

        // 検索処理
        $searchData = [$searchPrimaryKey => $key];

        $reqData = [
            'search_info' => $searchData,
        ];

        $reqData['m_account_id'] = $this->esmSessionManager->getAccountId();

        $requestData = [
            'request' => $reqData
        ];

        $result = [];
        $responseRows = $this->esm2ApiManager->executeSearchApi('searchDeliveryInfo', Esm2SubSys::WAREHOUSE, $searchData, $reqData);

        Log::info('responseRows: '. print_r($responseRows, true));
        return $responseRows['search_result'][0];
    }
}
