<?php

namespace App\Modules\Order\Base;

use App\Enums\Esm2SubSys;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;
use App\Enums\ProgressTypeEnum;
use Illuminate\Support\Facades\Log;

class UpdateApiOrderProgress implements UpdateApiOrderProgressInterface
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

    public function execute(array $params)
    {
        // 出荷済登録処理
        $requestData = [
            't_order_hdr_id'			=>	$params['t_order_hdr_id'],
            'progress_type'				=>	$params['progress_type'],
            'progress_type_self_change'	=>	1,
            'update_operator_id'		=>	$params['update_operator_id'],
        ];

        // キャンセルの場合
        if ($params['progress_type'] == ProgressTypeEnum::Cancelled->value) {
            $requestData['cancel_type'] = $params['cancel_type'];
            $requestData['cancel_note'] = $params['cancel_note'];
        }

        $extendData = [
            'm_account_id' => $this->esmSessionManager->getAccountId(),
            'operator_id' => $this->esmSessionManager->getOperatorId(),
            'feature_id' => ''
        ];

        $registerCheckData = $this->esm2ApiManager->executeRegisterApi('registerOrderProgress', Esm2SubSys::ORDER_API, $requestData, $extendData);

        return $registerCheckData;
    }
}
