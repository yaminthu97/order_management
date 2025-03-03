<?php

namespace App\Modules\Customer\Gfh1207;

use App\Exceptions\DataNotFoundException;
use App\Models\Cc\Gfh1207\CustModel;
use App\Modules\Common\CommonModule;
use App\Modules\Customer\Base\StoreCustomerInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreCustomer extends CommonModule implements StoreCustomerInterface
{
    public $current_id;
    public const DEFAULT_ERROR_CODE = 0;

    //DBへの保存コード
    public function execute(array $data)
    {
        try {
            //トランザクション追加
            DB::transaction(function () use ($data) {

                $accountId = $this->getAccountId();
                $operatorId = $this->getOperatorId();

                if (empty($data['m_cust_id'])) {
                    $model = new CustModel();
                    $model->m_account_id = $accountId;
                    $model->entry_operator_id = $operatorId;
                    $model->update_operator_id = $operatorId;
                } else {
                    $model = CustModel::findOrFail($data['m_cust_id']);
                }

                if (empty($model) || $model->m_account_id != $accountId) {
                    throw new DataNotFoundException("顧客ID{{" . $data['m_cust_id'] . "}}が見つかりません。");
                }

                //保存
                $model->fill($data);
                $model->save();
                $this->current_id = $model->m_cust_id;
            });
        } catch (\Throwable $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }

    public function getId()
    {
        return $this->current_id;
    }
}
