<?php

namespace App\Modules\Customer\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Cc\Gfh1207\CustCommunicationDtlModel;
use App\Modules\Customer\Base\StoreCustCommunicationDtlInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StoreCustCommunicationDtl implements StoreCustCommunicationDtlInterface
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

    public function execute(array $fillData): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($fillData) {
                $accountId = $this->esmSessionManager->getAccountId();
                $operatorId = $this->esmSessionManager->getOperatorId();

                $new = new CustCommunicationDtlModel();

                $errors = [];
                $new->m_account_id = $accountId;
                $new->entry_operator_id = $operatorId;
                $new->update_operator_id = $operatorId;
                $new->t_cust_communication_id = $fillData['t_cust_communication_id'];
                $new->contact_way_type = $fillData['contact_way_type'];
                $new->status = $fillData['status'];
                $new->category = $fillData['category'];
                $new->receive_detail = $fillData['receive_detail'];
                $new->receive_datetime = $fillData['receive_datetime'];
                $new->receive_operator_id = $fillData['receive_operator_id'];
                $new->receive_datetime = $fillData['receive_datetime'];
                $new->escalation_operator_id = $fillData['escalation_operator_id'];
                $new->answer_detail = $fillData['answer_detail'];
                $new->answer_datetime = $fillData['answer_datetime'];
                $new->answer_operator_id = $fillData['answer_operator_id'];
                if(count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                }

                // 保存
                $new->save();
                return $new;
            });

        } catch(\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new;
    }

}
