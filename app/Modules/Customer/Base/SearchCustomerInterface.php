<?php

namespace App\Modules\Customer\Base;

interface SearchCustomerInterface
{
    /**
     * 検索処理
     */
    public function execute(array $conditions, array $options);
}
