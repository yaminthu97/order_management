<?php

namespace App\Modules\Order\Base;

use App\Enums\Esm2SubSys;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;

class RegisterOrderProgress implements RegisterOrderProgressInterface
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
        // 進捗区分変更API
        $registerInfo = [
            't_order_hdr_id' => $params['t_order_hdr_id'], // 受注基本ID
            'progress_type' => $params['progress_type'], // 変更後の進捗区分
            'cancel_type' => $params['cancel_type'], // 取消理由 キャンセルに変更する場合
            'cancel_note' => $params['cancel_note'], // 取消備考 キャンセルに変更する場合
            'progress_type_self_change' => $params['progress_type_self_change'] ?? 1, // NULL：自動、1：手動
            'update_operator_id' => $this->esmSessionManager->getOperatorId(),
        ];

        $registerData['m_account_id'] = $this->esmSessionManager->getAccountId();

        $registerData = ['register_info' => $registerInfo];

        $response = $this->esm2ApiManager->executeRegisterApi('registerOrderProgress', Esm2SubSys::ORDER, $registerData);

        return json_decode($response, true);
    }
}
