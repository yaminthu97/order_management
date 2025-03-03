<?php
namespace App\Modules\Sample\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Cc\Base\CustModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindSample implements FindSampleInterface
{
    public function execute(string|int $id): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try{
            $query = CustModel::query();
            $query->with([
            ]);
            $customer = $query->findOrFail($id);

        }catch(ModelNotFoundException $e){
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => 'サンプル情報', 'id' => $id]), 0, $e);
        }

        ModuleCompleted::dispatch(__CLASS__, [$customer->toArray()]);
        return $customer;
    }
}
