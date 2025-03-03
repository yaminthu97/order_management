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
use Illuminate\Support\Facades\DB;

use App\Modules\Master\Base\SaveCampaignModuleInterface;

class SaveCampaignModule extends CommonModule implements SaveCampaignModuleInterface
{
    //DBへの保存コード
    public function execute(array $data){
        //トランザクション追加
        DB::transaction(function () use ($data) {
            $accountId = $this->getAccountId();
            $operatorId = $this->getOperatorId();
            if(empty($data['m_campaign_id'])){
                $model = new CampaignModel();
                $model->m_account_id = $accountId;
                $model->update_operator_id = $operatorId;
            } else {
                $model = CampaignModel::find($data['m_campaign_id']);
                $model->update_operator_id = $operatorId;
            }
            if(empty($model) || $model->m_account_id != $accountId){
                throw new DataNotFoundException("キャンペーンID{{". $data['m_campaign_id']."}}が見つかりません。");
            }
            //保存
            $model->campaign_name           = $data['campaign_name'];
            $model->delete_flg              = $data['delete_flg'];
            $model->from_date               = $data['from_date'];
            $model->to_date                 = $data['to_date'];
            $model->giving_condition_amount = $data['giving_condition_amount'];
            $model->giving_condition_every  = $data['giving_condition_every'];
            $model->giving_page_cd          = $data['giving_page_cd']; 
            $model->save();

        });
    }
}