<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Gfh1207\DeliveryFeeModel;
use App\Modules\Master\Base\NotifyDeliveryFeesInterface;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class NotifyDeliveryFees implements NotifyDeliveryFeesInterface
{
    public function execute(array $fillData, array $exFillData, int|string|null $id): Model
    {

        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {
            $model = DeliveryFeeModel::findOrNew($id);
            $model->fill($fillData);
        } catch (Throwable $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$model->toArray()]);
        return $model;
    }
}
