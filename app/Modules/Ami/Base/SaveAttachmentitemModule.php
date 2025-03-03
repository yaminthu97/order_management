<?php

namespace App\Modules\Ami\Base;

use App\Exceptions\DataNotFoundException;
use App\Models\Ami\Gfh1207\AttachmentitemModel;
use App\Modules\Common\CommonModule;
use Config;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Modules\Ami\Base\SaveAttachmentitemModuleInterface;

class SaveAttachmentitemModule extends CommonModule implements SaveAttachmentitemModuleInterface
{
    //DBへの保存コード
    public function execute(array $data){
        //トランザクション追加
        DB::transaction(function () use ($data) {
            $accountId = $this->getAccountId();
            $operatorId = $this->getOperatorId();
            if(empty($data['m_ami_attachment_item_id'])){
                $model = new AttachmentitemModel();
                $model->m_account_id = $accountId;
                $model->update_operator_id = $operatorId;
            } else {
                $model = AttachmentitemModel::find($data['m_ami_attachment_item_id']);
                $model->update_operator_id = $operatorId;
            }
            if(empty($model) || $model->m_account_id != $accountId){
                throw new DataNotFoundException("付属品マスタID{{". $data['m_ami_attachment_item_id']."}}が見つかりません。");
            }
            if(empty($model)){
                throw new DataNotFoundException("付属品マスタID{{". $data['m_ami_attachment_item_id']."}}が見つかりません。");
            }
            //保存
            $model->category_id             = $data['category_id'];
            $model->attachment_item_cd      = $data['attachment_item_cd'];
            $model->attachment_item_name    = $data['attachment_item_name'];
            $model->delete_flg              = $data['delete_flg'];
            $model->display_flg             = $data['display_flg'];
            $model->invoice_flg             = $data['invoice_flg'];
            $model->reserve1                = $data['reserve1'];
            $model->reserve2                = $data['reserve2'];
            $model->reserve3                = $data['reserve3'];
            $model->save();

        });
    }
}