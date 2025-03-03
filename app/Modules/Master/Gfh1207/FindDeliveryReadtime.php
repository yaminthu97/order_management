<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Gfh1207\DeliveryReadtimeModel;
use App\Modules\Master\Base\FindDeliveryReadtimeInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindDeliveryReadtime implements FindDeliveryReadtimeInterface
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
    public function execute(string|int $id)
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));

        $m_account_id = $this->esmSessionManager->getAccountId();

        try {
            // Fetch records based on the condition
            $deliveryReadtime = DeliveryReadtimeModel::where([
                'm_account_id' => $m_account_id,
                'm_warehouses_id' => $id,
            ])->get();

            // If no records found, throw a custom exception
            if ($deliveryReadtime->isEmpty()) {
                throw new ModelNotFoundException();
            }

            // Prepare the structured result as an array
            $structuredData = [
                'delivery_readtime' => []
            ];

            $masterPack = DeliveryReadtimeModel::where([
                'm_account_id' => $m_account_id,
                'm_warehouses_id' => $id,
                'm_delivery_types_id' => 1
            ])->value('master_pack_apply_flg');

            // Group the data by m_delivery_types_id
            foreach ($deliveryReadtime as $item) {
                $m_delivery_types_id = $item->m_delivery_types_id;
                $m_prefecture_id = $item->m_prefecture_id;
                $delivery_readtime = $item->delivery_readtime;

                // Ensure the $m_delivery_types_id exists in the structure
                if (!isset($structuredData['delivery_readtime'][$m_delivery_types_id])) {
                    $structuredData['delivery_readtime'][$m_delivery_types_id] = [];
                }

                // Set the delivery_readtime for the specific m_prefecture_id under the m_delivery_types_id
                $structuredData['delivery_readtime'][$m_delivery_types_id][$m_prefecture_id] = $delivery_readtime;
            }

        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            return null;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        // Dispatch successful completion event
        ModuleCompleted::dispatch(__CLASS__, [$structuredData]);

        return [
            'delivery_readtime' => $structuredData['delivery_readtime'],
            'master_pack_apply_flg' => $masterPack,
        ];
    }
}
