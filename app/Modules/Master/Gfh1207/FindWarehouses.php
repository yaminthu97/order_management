<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Warehouse\Gfh1207\WarehouseModel;
use App\Modules\Master\Base\FindWarehousesInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindWarehouses implements FindWarehousesInterface
{
    public function execute(string|int $id): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try {
            $query = WarehouseModel::query();
            $query->with([]);
            $warehouse = $query->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '倉庫マスタ', 'id' => $id]), 0, $e);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        ModuleCompleted::dispatch(__CLASS__, [$warehouse->toArray()]);
        return $warehouse;
    }
}
