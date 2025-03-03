<?php

namespace App\Modules\Master\Base;

interface SearchPostalCodeInterface
{
    /**
     * 取得処理
     */
    public function execute(array $conditions=[], array $options=[]);
}
