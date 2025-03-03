<?php

namespace App\Modules\Customer\Base;

interface StoreCustomerInterface
{
    /**
     * 作成と更新処理
     */
    public function execute(array $conditions);

    /**
     * 作成ID取得処理
     */
    public function getId();

}
