<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Base\DeliveryUniqueSettingSeinoModel;
use App\Models\Master\Base\DeliveryUniqueSettingYamatoModel;
use App\Models\Master\Gfh1207\DeliveryTypeModel;
use App\Modules\Master\Base\NotifyDeliveryTypeInterface;
use Throwable;

class NotifyDeliveryType implements NotifyDeliveryTypeInterface
{
    public function execute(array $fillData, array $exFillData, int|string|null $id): array
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {
            // Fill data into DeliveryTypeModel
            $deliveryTypeModel = DeliveryTypeModel::findOrNew($id);
            $deliveryTypeModel->fill($fillData);

            // Ensure delete flag is excluded from further processing
            unset($fillData['delete_flg']);

            // Fill data into DeliveryUniqueSettingSeinoModel
            $deliveryUniqueSettingSeinoModel = DeliveryUniqueSettingSeinoModel::firstOrNew(['m_delivery_types_id' => $id]);
            $deliveryUniqueSettingSeinoModel->fill($fillData);

            // Fill data into DeliveryUniqueSettingYamatoModel
            $DeliveryUniqueSettingYamatoModel = DeliveryUniqueSettingYamatoModel::firstOrNew(['m_delivery_types_id' => $id]);
            $DeliveryUniqueSettingYamatoModel->fill($fillData);

        } catch (Throwable $e) {
            // Handle exception and dispatch ModuleFailed event
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }
        $model = [
            'delivery_type' => $deliveryTypeModel->toArray(),
            'seino' => $deliveryUniqueSettingSeinoModel->toArray(),
            'yamato' => $DeliveryUniqueSettingYamatoModel->toArray(),
        ];
        // Dispatch ModuleCompleted event and return models
        ModuleCompleted::dispatch(__CLASS__, [
            'delivery_type' => $deliveryTypeModel->toArray(),
            'seino' => $deliveryUniqueSettingSeinoModel->toArray(),
            'yamato' => $DeliveryUniqueSettingYamatoModel->toArray(),
        ]);
        return $model;
    }
}
