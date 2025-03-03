<?php

namespace App\Modules\Master\Base;

use App\Enums\Esm2SubSys;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\Log;

class GetEcs implements GetEcsInterface
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
    protected $connectionApiUrl = 'searchEcs';

    /**
     * 表示名(フォーマット用)
     */
    protected $displayName = 'm_ec_name';

    /**
     * 値(フォーマット用)
     */
    protected $valueName = 'm_ecs_id';

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
            'feature_id' => 'master/ecs/list',
        ];

        $requestData =  [
            'delete_flg' => '0'
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
            $valueArray[$resRow[$this->displayName]] = $resRow[$this->valueName];
        }
        return $valueArray;
    }

}
