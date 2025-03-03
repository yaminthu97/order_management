<?php

namespace App\Modules\Master\Base;

interface SearchShopsInterface
{
    /**
     * 店舗情報取得
     *
     * @param int $shopId 店舗ID
     */
    public function execute(array $condtions = [], array $options = []);
}
