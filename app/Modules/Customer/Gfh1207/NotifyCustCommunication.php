<?php

namespace App\Modules\Customer\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Cc\Gfh1207\CustCommunicationModel;
use App\Modules\Customer\Base\NotifyCustCommunicationInterface;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class NotifyCustCommunication implements NotifyCustCommunicationInterface
{
    public function execute(array $fillData, array $exFillData, int|string|null $id): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {
            $model = CustCommunicationModel::findOrNew($id);
            $model->fill($fillData);

        } catch(Throwable $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$model->toArray()]);
        return $model;
    }
}
