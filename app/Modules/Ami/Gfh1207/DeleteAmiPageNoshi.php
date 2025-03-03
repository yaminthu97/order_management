<?php

namespace App\Modules\Ami\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Ami\Base\AmiPageNoshiModel;
use App\Modules\Ami\Base\DeleteAmiPageNoshiInterface;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteAmiPageNoshi implements DeleteAmiPageNoshiInterface
{
    public function __construct(
        protected EsmSessionManager $sessionManager
    ) {
    }

    public function execute(int $id)
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try {
            // トランザクション開始
            DB::transaction(function () use ($id) {
                $query = AmiPageNoshiModel::findOrFail($id);
                // データを削除
                $query->delete();

            });
        } catch (Throwable $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$id]);
        return true;
    }
}
