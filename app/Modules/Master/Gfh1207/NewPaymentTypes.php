<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Gfh1207\PaymentTypeModel;
use App\Modules\Master\Base\NewPaymentTypesInterface;
use Illuminate\Database\Eloquent\Model;

class NewPaymentTypes implements NewPaymentTypesInterface
{
    public function execute(array $fillData = [], array $exFillData = []): Model
    {
        $model = new PaymentTypeModel();
        $model->fill($fillData);
        foreach ($exFillData as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }
}
