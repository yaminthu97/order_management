<?php

namespace App\Modules\Customer\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Cc\Gfh1207\CustModel;
use App\Modules\Customer\Base\FindCustomerInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindCustomer implements FindCustomerInterface
{
    public const DEFAULT_ERROR_CODE = 0;
    protected $esmSessionManager;

    public function __construct()
    {
        $this->esmSessionManager = new EsmSessionManager();
    }

    public function execute(int $id)
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try {
            $query = CustModel::query();
            $customer = $query->findOrFail($id);

            // Check if m_account_id is not equal to 1
            if ($customer->m_account_id !== $this->esmSessionManager->getAccountId()) {
                // 企業アカウントIDが指定されていない場合は、エラー
                throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => 'サンプル情報', 'id' => $id]));
            }

        } catch(ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => 'サンプル情報', 'id' => $id]), self::DEFAULT_ERROR_CODE, $e);
        }
        ModuleCompleted::dispatch(__CLASS__, [$customer->toArray()]);
        return $customer;
    }
}
