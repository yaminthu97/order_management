<?php
namespace App\Modules\Order\Base;

/**
 * 出荷情報取得インターフェース
 */
interface FindOrderInterface
{
    /**
     * 出荷情報取得
     *
     * @param int $orderId 出荷ID
     */
    public function execute(?int $orderId);
}
