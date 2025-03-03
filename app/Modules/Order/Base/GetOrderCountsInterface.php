<?php

namespace App\Modules\Order\Base;

/**
 * 受注件数取得インターフェース
 */
interface GetOrderCountsInterface
{
    /**
     * 受注件数取得
     *
     */
    public function execute(array $params);
}
