<?php
namespace App\Modules\Customer\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Cc\Gfh1207\CustomerModel;
use App\Modules\Customer\Base\FindCustomerInfoInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindCustomerInfo implements FindCustomerInfoInterface
{
    public function execute(int $id)
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try{
            $query = CustomerModel::query();
            $query->with([
                'custOrderSum',
            ]);
            $customer = $query->findOrFail($id);

        }catch(ModelNotFoundException $e){
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '顧客情報', 'id' => $id]), 0, $e);
        }

        ModuleCompleted::dispatch(__CLASS__, [$customer->toArray()]);
        return $customer;
    }
}
