<?php

namespace App\Modules\Ami\Base;

interface StoreAmiPageNoshiInterface
{
    /**
     * 保存処理
     */
    public function execute(array $conditions);

}
