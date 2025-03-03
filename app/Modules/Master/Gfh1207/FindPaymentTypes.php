<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Master\Gfh1207\PaymentTypeModel;
use App\Modules\Master\Base\FindPaymentTypesInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindPaymentTypes implements FindPaymentTypesInterface
{
    public function execute(string|int $id): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try {
            $query = PaymentTypeModel::query();
            $query->with([]);
            $customer = $query->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '支払方法マスタ', 'id' => $id]), 0, $e);
        } catch (\Exception $e) {
            throw new Exception($e);
        }

        ModuleCompleted::dispatch(__CLASS__, [$customer->toArray()]);
        return $customer;
    }
}
