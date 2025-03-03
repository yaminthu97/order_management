<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Gfh1207\DeliveryFeeModel;
use App\Modules\Master\Base\StoreDeliveryFeesInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StoreDeliveryFees implements StoreDeliveryFeesInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    public function __construct(EsmSessionManager $esmSessionManager)
    {
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute(array $fillData): bool
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {
            $newRecords = DB::transaction(function () use ($fillData) {
                $accountId = $this->esmSessionManager->getAccountId();
                $operatorId = $this->esmSessionManager->getOperatorId();

                $errors = [];
                $savedRecords = [];

                $warehouseId = $fillData['m_warehouses_id'];

                foreach ($fillData['delivery_fee'] as $m_delivery_types_id => $delivery_fees) {
                    foreach ($delivery_fees as $m_prefecture_id => $delivery_fee) {
                        // Create a new model instance
                        $record = new DeliveryFeeModel();
                        $record->m_account_id = $accountId;
                        $record->delete_flg = 0;
                        $record->m_warehouses_id = $warehouseId;
                        $record->m_delivery_types_id = $m_delivery_types_id;
                        $record->m_prefecture_id = $m_prefecture_id;
                        $record->delivery_fee = $delivery_fee ?? 0;
                        $record->entry_operator_id = $operatorId;
                        $record->update_operator_id = $operatorId;

                        // Save the record
                        $record->save();

                        $savedRecords[] = $record;
                    }
                }


                // Throw exception if there are validation errors
                if (count($errors) > 0) {
                    throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                }

                return true;
            });
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, ['savedRecords' => $newRecords]);
        return $newRecords ? true : false;
    }
}
