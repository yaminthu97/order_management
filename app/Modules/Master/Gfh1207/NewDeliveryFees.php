<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Gfh1207\DeliveryFeeModel;
use App\Modules\Master\Base\NewDeliveryFeesInterface;
use Illuminate\Database\Eloquent\Model;

class NewDeliveryFees implements NewDeliveryFeesInterface
{
    public function execute(array $fillData = [], array $exFillData = []): Model
    {
        $model = new DeliveryFeeModel();
        $model->fill($fillData);
        foreach ($exFillData as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }
}
