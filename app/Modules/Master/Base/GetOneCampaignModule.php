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

use App\Modules\Master\Base\GetOneCampaignModuleInterface;


class GetOneCampaignModule extends CommonModule implements GetOneCampaignModuleInterface
{

    public function execute($id){
        $query = CampaignModel::query();
        $query->where('m_account_id',$this->getAccountId());
        $query->where('m_campaign_id',$id);
        return $query->first();
    }
}