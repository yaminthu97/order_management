<?php

namespace App\Modules\Order\Base;

interface UpdateCampaignItemInterface
{
    /**
     * 登録済み受注に対するキャンペーン商品追加処理
     * 
     * @param int $orderHdrId 受注ヘッダーID
     * @param int $orderDestinationId 受注配送先ID
     * @return int
     */
    public function execute(int $orderHdrId, int $orderDestinationId): int;
}
