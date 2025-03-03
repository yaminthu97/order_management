<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Gfh1207\OperatorModel;
use App\Modules\Master\Base\NewOperatorsInterface;
use Illuminate\Database\Eloquent\Model;

class NewOperators implements NewOperatorsInterface
{
    public function execute(array $fillData = [], array $exFillData = []): Model
    {
        $model = new OperatorModel();
        $model->fill($fillData);
        foreach ($exFillData as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }
}
