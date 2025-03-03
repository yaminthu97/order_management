<?php

namespace App\Modules\Order\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Gfh1207\OrderTagModel;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class NotifyOrderTagMaster implements NotifyOrderTagMasterInterface
{
    public function execute(array $fillData, array $exFillData, int|string|null $id): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {
            $model = OrderTagModel::findOrNew($id);
            $model->fill($fillData);

        } catch (Throwable $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$model->toArray()]);
        return $model;
    }
}
