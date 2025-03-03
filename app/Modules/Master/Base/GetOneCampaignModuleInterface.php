<?php

namespace App\Modules\Master\Base;

interface GetOneCampaignModuleInterface
{
    /**
     * 取得処理(1件)
     */
    public function execute($id);
}