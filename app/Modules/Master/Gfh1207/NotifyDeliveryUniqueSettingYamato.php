<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Base\DeliveryUniqueSettingYamatoModel;
use App\Modules\Master\Base\NotifyDeliveryUniqueSettingYamatoInterface;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class NotifyDeliveryUniqueSettingYamato implements NotifyDeliveryUniqueSettingYamatoInterface
{
    public function execute(array $fillData, array $exFillData, int|string|null $id): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {

            $model = DeliveryUniqueSettingYamatoModel::firstOrNew(['m_delivery_types_id' => $id]);
            $model->fill($fillData);

        } catch (Throwable $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$model->toArray()]);
        return $model;
    }
}
