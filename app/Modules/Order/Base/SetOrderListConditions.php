<?php

namespace App\Modules\Order\Base;

use App\Enums\Esm2SubSys;
use App\Models\Order\Base\OrderListCondModel;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class SetOrderListConditions implements SetOrderListConditionsInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;
    public function __construct(EsmSessionManager $esmSessionManager)
    {
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute(array $conditions = [])
    {
        // トランザクション開始
        $orderListCond = DB::transaction(function () use ($conditions) {
            try{
                if (isset($conditions['m_order_list_cond_id']) && strlen($conditions['m_order_list_cond_id']) > 0 &&
                    isset($conditions['delete']) && strlen($conditions['delete']) > 0) {
                    // 削除
                    $orderListCond = OrderListCondModel::findOrFail($conditions['m_order_list_cond_id']);
                    $orderListCond->delete_operator_id = $this->esmSessionManager->getOperatorId();
                    $orderListCond->delete_timestamp = Carbon::now();
                } elseif (isset($conditions['m_order_list_cond_id']) && strlen($conditions['m_order_list_cond_id']) > 0) {
                    // 更新
                    $orderListCond = OrderListCondModel::findOrFail($conditions['m_order_list_cond_id']);
                    $orderListCond->order_list_cond = json_encode($conditions['order_list_cond']);
                    $orderListCond->public_flg = $conditions['public_flg'];
                    $orderListCond->update_operator_id = $this->esmSessionManager->getOperatorId();
                    if (isset($conditions['order_list_cond_name']) && strlen($conditions['order_list_cond_name']) > 0) {
                        $orderListCond->order_list_cond_name = $conditions['order_list_cond_name'];
                    }
                    if (isset($conditions['public_flg']) && strlen($conditions['public_flg']) > 0) {
                        $orderListCond->public_flg = $conditions['public_flg'];
                    }
                } else {
                    // 新規
                    $orderListCond = new OrderListCondModel();
                    $orderListCond->m_account_id = $this->esmSessionManager->getAccountId();
                    $orderListCond->entry_operator_id = $this->esmSessionManager->getOperatorId();
                    $orderListCond->update_operator_id = $this->esmSessionManager->getOperatorId();
                    $orderListCond->order_list_cond = json_encode($conditions['order_list_cond']);
                    $orderListCond->public_flg = $conditions['public_flg'];
                    $orderListCond->delete_operator_id = 0;
                    $orderListCond->order_list_cond_name = $conditions['order_list_cond_name'];
                }
                $orderListCond->save();

            }catch(ModelNotFoundException $e){
                throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '受注検索', 'id' => $conditions['m_order_list_cond_id']]), 0, $e);
            }

            return $orderListCond;
        });

        return $orderListCond;
    }
}
