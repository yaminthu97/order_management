<?php

namespace App\Modules\Order\Base;

use App\Models\Order\Base\OrderListCondModel;

use App\Services\EsmSessionManager;

class GetOrderListConditions implements GetOrderListConditionsInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    public function __construct(
        EsmSessionManager $esmSessionManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute(array $conditions=[], array $options=[])
    {
        $query = OrderListCondModel::query();

        // 検索条件
        $query->where('m_account_id', $this->esmSessionManager->getAccountId());
        $query->where('delete_operator_id', 0);
        // 同一オペレーター、または公開フラグが1
        $query->where(function ($query) {
            $query->where('entry_operator_id', $this->esmSessionManager->getOperatorId())
                ->orWhere('public_flg', 1);
        });
        // $conditions['m_order_list_cond_id'] の指定がある場合は
        if (isset($conditions['m_order_list_cond_id']) && strlen($conditions['m_order_list_cond_id']) > 0) {
            $query->where('m_order_list_cond_id', $conditions['m_order_list_cond_id']);
        }

        return $query->get()->toArray();
    }
}
