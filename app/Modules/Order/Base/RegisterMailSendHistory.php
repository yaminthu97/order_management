<?php

namespace App\Modules\Order\Base;

use App\Enums\Esm2SubSys;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;

class RegisterMailSendHistory implements RegisterMailSendHistoryInterface
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
        // メール送信登録API
        $registerInfo = [
            't_order_hdr_id' => $params['t_order_hdr_id'] ?? null,
            't_deli_hdr_id' => $params['t_deli_hdr_id'] ?? null,
            'm_cust_id' => $params['m_cust_id'] ?? null,
            'mail_from_name' => $params['mail_from_name'] ?? null,
            'mail_from' => $params['mail_from'] ?? null,
            'mail_to' => $params['mail_to'] ?? null,
            'mail_cc' => $params['mail_cc'] ?? null,
            'mail_bcc' => $params['mail_bcc'] ?? null,
            'mail_title' => $params['mail_title'] ?? null,
            'm_email_templates_id' => $params['m_email_templates_id'] ?? null,
            'mail_text' => $params['mail_text'] ?? null,
            'mail_send_request_id' => $params['mail_send_request_id'] ?? null,
            'entry_operator_id' => $this->esmSessionManager->getOperatorId(),
        ];

        $registerData['m_account_id'] = $this->esmSessionManager->getAccountId();

        $registerData = ['register_info' => $registerInfo];

        $response = $this->esm2ApiManager->executeRegisterApi('registerMailSendHistory', Esm2SubSys::ORDER, $registerData);

        return json_decode($response, true);
    }
}
