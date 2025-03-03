<?php

namespace App\Modules\Common\Base;

interface SearchCampaignsInterface
{
    /**
     * キャンペーンマスタ取得
     * @param int
     */
    public function execute(array $condtions = [], array $options = []);
}
