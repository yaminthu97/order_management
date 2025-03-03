<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Base\ReportTemplateModel;
use App\Modules\Master\Base\UpdateTemplateMasterInterface;
use App\Services\EsmSessionManager;
use App\Services\FileUploadManager;
use Illuminate\Support\Facades\DB;

class UpdateTemplateMaster implements UpdateTemplateMasterInterface
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

            // テンプレートマスターを作成する
            $new = DB::transaction(function () use ($id, $data) {
                $operatorId = $this->esmSessionManager->getOperatorId();
                $new = ReportTemplateModel::findOrFail($id);

                // データを設定
                $new->template_name = $data['template_name'];
                $new->template_file_name = $data['ref_file_path']->getClientOriginalName();
                $new->update_operator_id = $operatorId;

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
