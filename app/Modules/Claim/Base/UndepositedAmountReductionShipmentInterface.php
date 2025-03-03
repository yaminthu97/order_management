<?php
namespace App\Modules\Claim\Base;

interface UndepositedAmountReductionShipmentInterface
{
    /**
     * 顧客受注累計更新
     *
     * @param int $accountId account ID
     * @param int $orderId 受注基本ID
     * @param int $orderId 出荷基本ID
     * @param int $value 入金額
     */
    public function execute(int $accountId, int $orderId, int $deliHdrId, int $value);
}
