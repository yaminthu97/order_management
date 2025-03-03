<?php

namespace App\Modules\Master\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Gfh1207\ItemnameTypeModel;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class NotifyItemnameType implements NotifyItemnameTypeInterface
{
    public function execute(array $fillData, array $exFillData, int|string|null $id): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {
            
            $model = ItemnameTypeModel::findOrNew($id);
            $model->fill($fillData);

        } catch (Throwable $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$model->toArray()]);
        return $model;
    }
}
