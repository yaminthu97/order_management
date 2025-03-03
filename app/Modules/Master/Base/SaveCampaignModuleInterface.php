<?php

namespace App\Modules\Master\Base;

interface SaveCampaignModuleInterface
{
    /**
     * 保存処理
     */
    public function execute(array $data);
}