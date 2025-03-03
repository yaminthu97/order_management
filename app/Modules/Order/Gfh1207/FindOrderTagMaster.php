<?php

namespace App\Modules\Order\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Master\Gfh1207\OrderTagModel;
use App\Modules\Order\Base\FindOrderTagMasterInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindOrderTagMaster implements FindOrderTagMasterInterface
{
    public function execute(string|int $id): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try {
            $query = OrderTagModel::query();
            $orderTagMaster = $query->findOrFail($id);

        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '受注タグ情報', 'id' => $id]), 0, $e);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        ModuleCompleted::dispatch(__CLASS__, [$orderTagMaster->toArray()]);
        return $orderTagMaster;
    }
}
