<?php

namespace App\Modules\Order\Base;

use App\Models\Master\Base\OrderTagModel;
use App\Services\EsmSessionManager;

class SearchOrderTagMaster implements SearchOrderTagMasterInterface
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

    /**
     * 受注情報取得
     *
     * @param int $orderId 受注ID
     */
    public function execute()
    {
        $query = OrderTagModel::query();

        // 検索条件
        $query->where('m_account_id', $this->esmSessionManager->getAccountId());
        // order by m_order_tag_sort
        $query->orderBy('m_order_tag_sort');

        return $query->get()->toArray();
    }
}
