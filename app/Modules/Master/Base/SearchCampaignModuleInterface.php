<?php

namespace App\Modules\Master\Base;

interface SearchCampaignModuleInterface
{
    /**
     * 取得処理
     */
    public function execute(array $conditions);
}