<?php

namespace App\Modules\Master\Base;

// interface GetSkusInterface
// {
//     /**
//      * 取得処理
//      */
//     public function execute($key, $itemId);
// }

use App\Exceptions\DataNotFoundException;
use App\Models\Master\Gfh1207\CampaignModel;
use App\Modules\Common\CommonModule;
use Config;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

use App\Modules\Master\Base\SearchCampaignModuleInterface;


class SearchCampaignModule extends CommonModule implements SearchCampaignModuleInterface
{

    public function execute(array $conditions)
    {

    }
}