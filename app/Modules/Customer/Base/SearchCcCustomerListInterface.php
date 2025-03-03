<?php

namespace App\Modules\Customer\Base;

interface SearchCcCustomerListInterface
{
    /**
     * 検索処理
     */
    public function execute(array $conditions);
}
