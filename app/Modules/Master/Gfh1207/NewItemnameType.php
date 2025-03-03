<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Gfh1207\ItemnameTypeModel;
use App\Modules\Master\Base\NewItemnameTypeInterface;
use Illuminate\Database\Eloquent\Model;

class NewItemnameType implements NewItemnameTypeInterface
{
    public function execute(array $fillData = [], array $exFillData = []): Model
    {
        $model = new ItemnameTypeModel();
        $model->fill($fillData);
        foreach ($exFillData as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }
}
