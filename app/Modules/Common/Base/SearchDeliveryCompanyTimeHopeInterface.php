<?php

namespace App\Modules\Common\Base;

interface SearchDeliveryCompanyTimeHopeInterface
{
    /**
     * 配送業者別希望時間帯
     *
     * @param array $params 検索条件
     */
    public function execute(array $conditions = [], array $options = []);
}
