<?php

namespace App\Modules\Order\Base;

interface AddCampaignItemInterface
{
    /**
     * キャンペーン商品追加処理
     */
    public function execute(array $req);
}
