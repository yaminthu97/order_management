<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Warehouse\Gfh1207\WarehouseModel;
use App\Modules\Master\Base\NewWarehousesInterface;
use Illuminate\Database\Eloquent\Model;

class NewWarehouses implements NewWarehousesInterface
{
    public function execute(array $fillData = [], array $exFillData = []): Model
    {
        $model = new WarehouseModel();
        $model->fill($fillData);
        foreach ($exFillData as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }
}
