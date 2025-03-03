<?php

namespace App\Modules\Common\Base;

interface SearchDeliveryTimeHopeInterface
{
    /**
     * 配送時間帯希望情報取得
     *
     * @param array $params 検索条件
     */
    public function execute(array $conditions = [], array $options = []);
}
