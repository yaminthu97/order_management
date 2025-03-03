<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Gfh1207\DeliveryReadtimeModel;
use App\Modules\Master\Base\NewDeliveryReadtimeInterface;
use Illuminate\Database\Eloquent\Model;

class NewDeliveryReadtime implements NewDeliveryReadtimeInterface
{
    public function execute(array $fillData = [], array $exFillData = []): Model
    {
        $model = new DeliveryReadtimeModel();
        $model->fill($fillData);
        foreach ($exFillData as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }
}
