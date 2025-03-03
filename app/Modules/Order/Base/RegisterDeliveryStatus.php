<?php

namespace App\Modules\Order\Base;

use App\Enums\Esm2SubSys;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;

class RegisterDeliveryStatus implements RegisterDeliveryStatusInterface
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

    public function execute(array $editRow)
    {
        // 出荷済登録処理
        $reqRegisterData = [
            'register_info' => $editRow,
        ];

        $reqRegisterData['m_account_id'] = $this->esmSessionManager->getAccountId();

        $requestData = ['request' => $reqRegisterData];

        $registerCheckData = $this->esm2ApiManager->executeRegisterApi('registerDeliveryStatus', Esm2SubSys::ORDER, $requestData);

        return json_decode($registerCheckData, true);
    }
}
