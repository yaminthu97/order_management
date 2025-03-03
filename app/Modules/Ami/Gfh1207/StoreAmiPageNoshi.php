<?php

namespace App\Modules\Ami\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\ModuleValidationException;
use App\Models\Ami\Base\AmiPageNoshiModel;
use App\Modules\Ami\Base\StoreAmiPageNoshiInterface;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\DB;

class StoreAmiPageNoshi implements StoreAmiPageNoshiInterface
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
    public function execute(array $data)
    {
        ModuleStarted::dispatch(__CLASS__, compact('data'));

        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($data) {
                $accountId = $this->esmSessionManager->getAccountId();
                $operatorId = $this->esmSessionManager->getOperatorId();

                $new = new AmiPageNoshiModel();

                $errors = [];
                // データを設定
                $new->m_ami_page_id = $data['m_ami_page_id'];
                $new->m_noshi_id = $data['m_noshi_id'];
                $new->m_noshi_format_id = $data['m_noshi_format_id'];

                $new->m_account_id = $accountId;
                $new->entry_operator_id = $operatorId;
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
