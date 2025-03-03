<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Master\Gfh1207\OperatorModel;
use App\Modules\Master\Base\FindOperatorsInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindOperators implements FindOperatorsInterface
{
    public function execute(string|int $id): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try {
            $query = OperatorModel::query();
            $query->with([]);
            $operator = $query->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '社員マスタ', 'id' => $id]), 0, $e);
        } catch (Exception $e) {
            throw new Exception($e);
        }

        ModuleCompleted::dispatch(__CLASS__, [$operator->toArray()]);
        return $operator;
    }
}
