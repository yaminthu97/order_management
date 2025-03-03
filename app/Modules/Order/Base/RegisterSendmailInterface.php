<?php

namespace App\Modules\Order\Base;

interface RegisterSendmailInterface
{
    /**
     * メール送信登録API
     */
    public function execute(array $params);
}
