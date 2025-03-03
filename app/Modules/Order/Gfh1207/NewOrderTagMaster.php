<?php

namespace App\Modules\Order\Base;

use App\Models\Master\Gfh1207\OrderTagModel;
use Illuminate\Database\Eloquent\Model;

class NewOrderTagMaster implements NewOrderTagMasterInterface
{
    public function execute(array $fillData = [], array $exFillData = []): Model
    {
        $model = new OrderTagModel();
        $model->fill($fillData);
        foreach ($exFillData as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }
}
