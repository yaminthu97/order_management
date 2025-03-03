<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Base\DeliveryUniqueSettingSeinoModel;
use App\Modules\Master\Base\NotifyDeliveryUniqueSettingSeinoInterface;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class NotifyDeliveryUniqueSettingSeino implements NotifyDeliveryUniqueSettingSeinoInterface
{
    public function execute(array $fillData, array $exFillData, int|string|null $id): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {

            $model = DeliveryUniqueSettingSeinoModel::firstOrNew(['m_delivery_types_id' => $id]);
            $model->fill($fillData);

        } catch (Throwable $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$model->toArray()]);
        return $model;
    }
}
