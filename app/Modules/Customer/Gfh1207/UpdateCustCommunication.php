<?php

namespace App\Modules\Customer\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Cc\Gfh1207\CustCommunicationModel;
use App\Modules\Customer\Base\UpdateCustCommunicationInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class UpdateCustCommunication implements UpdateCustCommunicationInterface
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

    public function execute(string|int $id, array $fillData, array $exFillData): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'));

        try {
            // トランザクション開始
            $custCommunication = DB::transaction(function () use ($id, $fillData, $exFillData) {
                $operatorId = $this->esmSessionManager->getOperatorId();
                $custCommunication = CustCommunicationModel::findOrFail($id);

                $errors = [];
                // fillできるデータを設定
                $custCommunication->fill($fillData);
                $custCommunication->update_operator_id = $operatorId;
                $custCommunication->save();
                return $custCommunication;
            });

        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'), $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '顧客対応履歴', 'id' => $id]), 0, $e);
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$custCommunication->toArray()]);
        return $custCommunication;
    }
}
