<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Base\DeliveryTypeModel;
use App\Modules\Master\Base\NewDeliveryTypeInterface;
use Illuminate\Database\Eloquent\Model;

class NewDeliveryType implements NewDeliveryTypeInterface
{
    public function execute(array $fillData = [], array $exFillData = []): Model
    {
        $model = new DeliveryTypeModel();
        $model->fill($fillData);
        foreach ($exFillData as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }
}
