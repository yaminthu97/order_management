<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Gfh1207\DeliveryReadtimeModel;
use App\Modules\Master\Base\UpdateDeliveryReadtimeInterface;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\DB;

class UpdateDeliveryReadtime implements UpdateDeliveryReadtimeInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    public function __construct(EsmSessionManager $esmSessionManager)
    {
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute(string|int $id, array $fillData, array $exFillData): bool
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        $success = true;

        try {
            DB::transaction(function () use ($id, $fillData) {
                $accountId = $this->esmSessionManager->getAccountId();
                $operatorId = $this->esmSessionManager->getOperatorId();

                $warehouseId = $id;

                foreach ($fillData['delivery_readtime'] as $m_delivery_types_id => $delivery_readtimes) {
                    foreach ($delivery_readtimes as $m_prefecture_id => $delivery_readtime) {
                        // Define the condition for finding the record
                        $conditions = [
                            'm_account_id' => $accountId,
                            'm_warehouses_id' => $warehouseId,
                            'm_delivery_types_id' => $m_delivery_types_id,
                            'm_prefecture_id' => $m_prefecture_id,
                        ];

                        $master_pack_apply_flg = ($m_delivery_types_id == 1) ? ($fillData['master_pack_apply_flg'] ?? 0) : 0;

                        // Define the values to be updated or created
                        $values = [
                            'delete_flg' => 0,
                            'master_pack_apply_flg' => $master_pack_apply_flg,
                            'delivery_readtime' => $delivery_readtime,
                            'update_operator_id' => $operatorId,
                        ];

                        // Use updateOrCreate to either update or insert the record
                        DeliveryReadtimeModel::updateOrCreate($conditions, $values);
                    }
                }
            });
        } catch (\Exception $e) {
            $success = false; // Mark the operation as failed
            ModuleFailed::dispatch(__CLASS__, compact('fillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, ['success' => $success]);

        return $success; // Return whether the operation succeeded
    }
}
