<?php

namespace App\Modules\Order\Base;

interface RegisterOrderTagAutoInterface
{
    /**
     * 受注タグ付与判定モジュール
     */
    public function execute(int $orderHdrId, int $autoTimming, ?int $account_id = null, ?int $operator_id = null);
}
