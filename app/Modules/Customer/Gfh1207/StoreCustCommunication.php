<?php

namespace App\Modules\Customer\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Cc\Gfh1207\CustCommunicationModel;
use App\Modules\Customer\Base\StoreCustCommunicationInterface;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\DB;

class StoreCustCommunication implements StoreCustCommunicationInterface
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

    public function execute(array $fillData, array $exFillData)
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData', 'exFillData'));

        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($fillData, $exFillData) {
                $accountId = $this->esmSessionManager->getAccountId();
                $operatorId = $this->esmSessionManager->getOperatorId();

                $new = new CustCommunicationModel();

                $errors = [];
                // fillできるデータを設定
                $new->fill($fillData);
                // fillできないデータを設定
                $new->m_account_id = $accountId;
                $new->entry_operator_id = $operatorId;
                $new->update_operator_id = $operatorId;
                if(count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                }

                // 保存
                $new->save();
                return $new;
            });

        } catch(\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new->t_cust_communication_id;
    }

}
