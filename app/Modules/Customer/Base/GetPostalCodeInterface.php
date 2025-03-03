<?php

namespace App\Modules\Customer\Base;

interface GetPostalCodeInterface
{
    /**
     * 検索処理
     */
    public function execute(array $conditions);
}
