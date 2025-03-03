<?php

namespace App\Modules\Order\Base;

use App\Models\Order\Base\OrderDestinationModel;
use App\Modules\Common\CommonModule;

use Config;
use DB;

/**
 * 受注配送先情報取得
 */
class FindOrderDestination extends CommonModule implements FindOrderDestinationInterface
{
    public function execute( $id ){
        $query = OrderDestinationModel::query()
        ->where('m_account_id', $this->getAccountId())
        ->where('t_order_destination_id', $id);
        return $query->first();
    }
}
