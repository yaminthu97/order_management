<?php
namespace App\Modules\Order\Base;

interface UpdateOrderInfoInterface
{
    /**
     * 受注照会更新
     *
     * @param array $params 更新情報
     */
    public function execute(array $params);
}
