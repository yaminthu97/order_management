<?php

namespace App\Modules\Order\Base;

interface AddBillingTypeInterface
{
    /**
     * 請求書送付先確認モジュール
     */
    public function execute(array $req);
}
