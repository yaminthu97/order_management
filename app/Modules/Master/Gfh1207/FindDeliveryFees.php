<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Gfh1207\DeliveryFeeModel;
use App\Modules\Master\Base\FindDeliveryFeesInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindDeliveryFees implements FindDeliveryFeesInterface
{
    public function execute(string|int $id)
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));

        try {
            // Fetch records based on the condition
            $deliveryReadtime = DeliveryFeeModel::where([
                'm_account_id' => 1, // Use the actual account ID if needed
                'm_warehouses_id' => $id,
            ])->get();

            // If no records found, throw a custom exception
            if ($deliveryReadtime->isEmpty()) {
                throw new ModelNotFoundException();
            }

            // Prepare the structured result as an array
            $structuredData = [
                'delivery_fee' => []
            ];

            // Group the data by m_delivery_types_id
            foreach ($deliveryReadtime as $item) {
                $m_delivery_types_id = $item->m_delivery_types_id;
                $m_prefecture_id = $item->m_prefecture_id;
                $delivery_fee = $item->delivery_fee;

                // Ensure the $m_delivery_types_id exists in the structure
                if (!isset($structuredData['delivery_fee'][$m_delivery_types_id])) {
                    $structuredData['delivery_fee'][$m_delivery_types_id] = [];
                }

                // Set the delivery_fee for the specific m_prefecture_id under the m_delivery_types_id
                $structuredData['delivery_fee'][$m_delivery_types_id][$m_prefecture_id] = $delivery_fee;
            }
        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            return null;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        // Dispatch successful completion event
        ModuleCompleted::dispatch(__CLASS__, [$structuredData]);

        // Return the structured array with 'delivery_fee' key included
        return ['delivery_fee' => $structuredData['delivery_fee']];
    }
}
