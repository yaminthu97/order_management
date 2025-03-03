<?php

namespace App\Modules\Customer\Base;

interface CheckCustomerInterface
{
    /**
     * 検索処理
     */
    public function execute(array $conditions);
}
