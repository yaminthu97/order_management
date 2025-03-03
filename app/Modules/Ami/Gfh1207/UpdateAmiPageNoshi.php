<?php

namespace App\Modules\Ami\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\ModuleValidationException;
use App\Models\Ami\Base\AmiPageNoshiModel;
use App\Modules\Ami\Base\UpdateAmiPageNoshiInterface;
use App\Services\EsmSessionManager;
use App\Services\FileUploadManager;
use Illuminate\Support\Facades\DB;

class UpdateAmiPageNoshi implements UpdateAmiPageNoshiInterface
{
    /**
    * ESMセッション管理クラス
    */
    protected $esmSessionManager;
    protected $fileUploadManager;

    public function __construct(
        EsmSessionManager $esmSessionManager,
        FileUploadManager $fileUploadManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
        $this->fileUploadManager = $fileUploadManager;
    }

    //DBへの保存コード
    public function execute(string|int $id, array $data)
    {
        ModuleStarted::dispatch(__CLASS__, compact('id', 'data'));

        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($id, $data) {
                $operatorId = $this->esmSessionManager->getOperatorId();
                $new = AmiPageNoshiModel::findOrFail($id);

                $errors = [];
                // データを設定
                $new->m_noshi_format_id = $data['m_noshi_format_id'];
                $new->update_operator_id = $operatorId;

                // Error handling
                if(count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new ModuleValidationException(__CLASS__, 0, null, $errors);
                }

                // 保存
                $new->save();
                return $new;
            });
        } catch(\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new;
    }

}
