<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Gfh1207\DeliveryFeeModel;
use App\Modules\Master\Base\UpdateDeliveryFeesInterface;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\DB;

class UpdateDeliveryFees implements UpdateDeliveryFeesInterface
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

        $success = true; // Track if all updates succeeded

        try {
            DB::transaction(function () use ($id, $fillData) {
                $accountId = $this->esmSessionManager->getAccountId();
                $operatorId = $this->esmSessionManager->getOperatorId();

                $warehouseId = $id;

                foreach ($fillData['delivery_fee'] as $m_delivery_types_id => $delivery_fees) {
                    foreach ($delivery_fees as $m_prefecture_id => $delivery_fee) {
                        // Define the condition for finding the record
                        $conditions = [
                            'm_account_id' => $accountId,
                            'm_warehouses_id' => $warehouseId,
                            'm_delivery_types_id' => $m_delivery_types_id,
                            'm_prefecture_id' => $m_prefecture_id,
                        ];

                        // Define the values to be updated or created
                        $values = [
                            'delete_flg' => 0,
                            'delivery_fee' => $delivery_fee ?? 0,
                            'update_operator_id' => $operatorId,
                        ];

                        // Use updateOrCreate to either update or insert the record
                        DeliveryFeeModel::updateOrCreate($conditions, $values);
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
