<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Master\Gfh1207\DeliveryTypeModel;
use App\Modules\Master\Base\FindDeliveryTypeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindDeliveryType implements FindDeliveryTypeInterface
{
    public function execute(string|int $id, array $option): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try {
            $query = DeliveryTypeModel::query();
            $query->with(
                $option['with']
            );
            $delivery = $query->findOrFail($id);

        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '配送方法
                情報', 'id' => $id]), 0, $e);
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw new \Exception($e);
        }

        ModuleCompleted::dispatch(__CLASS__, [$delivery->toArray()]);
        return $delivery;
    }
}
