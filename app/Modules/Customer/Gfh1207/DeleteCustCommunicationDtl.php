<?php

namespace App\Modules\Customer\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Cc\Gfh1207\CustCommunicationDtlModel;
use App\Modules\Customer\Base\DeleteCustCommunicationDtlInterface;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteCustCommunicationDtl implements DeleteCustCommunicationDtlInterface
{
    public function __construct(
        protected EsmSessionManager $sessionManager
    ) {
    }

    public function execute(string|int $id): bool
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try {
            // トランザクション開始
            DB::transaction(function () use ($id) {
                $deleteCustCommunicationDtl = CustCommunicationDtlModel::findOrFail($id);
                $deleteCustCommunicationDtl->delete_operator_id = $this->sessionManager->getOperatorId();
                $deleteCustCommunicationDtl->delete_timestamp = now();
                $deleteCustCommunicationDtl->save();
            });
        } catch (Throwable $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$id]);
        return true;
    }
}
