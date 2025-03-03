<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Warehouse\Gfh1207\WarehouseModel;
use App\Modules\Master\Base\NotifyWarehousesInterface;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class NotifyWarehouses implements NotifyWarehousesInterface
{
    public function execute(array $fillData, array $exFillData, int|string|null $id): Model
    {

        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {
            $model = WarehouseModel::findOrNew($id);
            $model->fill($fillData);
        } catch (Throwable $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$model->toArray()]);
        return $model;
    }
}
