<?php

namespace App\Modules\Master\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Master\Gfh1207\ItemnameTypeModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindItemnameType implements FindItemnameTypeInterface
{
    public function execute(string|int $id): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try {
            $query = ItemnameTypeModel::query();
            $itemType = $query->findOrFail($id);

        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '項目名称情報', 'id' => $id]), 0, $e);
        } catch (\Exception $e) {
            throw new Exceptions($e);
        }

        ModuleCompleted::dispatch(__CLASS__, [$itemType->toArray()]);
        return $itemType;
    }
}
