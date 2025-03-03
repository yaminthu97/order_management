<?php

namespace App\Modules\Ami\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\ModuleValidationException;
use App\Models\Ami\Base\AmiPageAttachmentItemModel;
use App\Modules\Ami\Base\UpdateAmiPageAttachmentInterface;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\DB;

class UpdateAmiPageAttachment implements UpdateAmiPageAttachmentInterface
{
    /**
    * ESMセッション管理クラス
    */
    protected $esmSessionManager;

    public function __construct(
        EsmSessionManager $esmSessionManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    //DBへの保存コード
    public function execute(string|int $id, array $data)
    {
        ModuleStarted::dispatch(__CLASS__, compact('id', 'data'));

        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($id, $data) {
                $operatorId = $this->esmSessionManager->getOperatorId();
                $new = AmiPageAttachmentItemModel::findOrFail($id);

                $errors = [];
                // データを設定
                $new->group_id = $data['attachment_item_group_id'];
                $new->item_vol = $data['attachment_item_vol'] ?? "0";
                $new->update_operator_id = $operatorId;

                // Error handling
                if(count($errors) > 0) {
                    throw new ModuleValidationException(__CLASS__, 0, null, $errors);
                }

                // 保存
                $new->save();
                return $new;
            });
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new;
    }

}
