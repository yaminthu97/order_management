<?php

namespace App\Modules\Order\Base;

use App\Enums\Esm2SubSys;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;

class RegisterSendmail implements RegisterSendmailInterface
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
            'm_ecs_id' => $params['m_ecs_id'] ?? null,
            'key' => $params['key'] ?? null,
            'from' => $params['from'] ?? null,
            'to' => $params['to'] ?? null,
            'cc' => $params['cc'] ?? null,
            'bcc' => $params['bcc'] ?? null,
            'subject' => $params['subject'] ?? null,
            'body' => $params['body'] ?? null,
            'mail_type' => $params['mail_type'] ?? null,
            'attach' => $params['attach'] ?? null,
            'smtp_info' => [],
        ];

        $registerData['m_account_id'] = $this->esmSessionManager->getAccountId();

        $registerData = ['register_info' => $registerInfo];

        $response = $this->esm2ApiManager->executeRegisterApi('registerSendmail', Esm2SubSys::GOTO, $registerData);

        return json_decode($response, true);
    }
}
