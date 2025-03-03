<?php

namespace App\Modules\Order\Base;

interface RegisterMailSendHistoryInterface
{
    /**
     * メール送信履歴登録API
     */
    public function execute(array $params);
}
