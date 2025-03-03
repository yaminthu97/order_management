<?php

namespace App\Modules\Ami\Base;

interface UpdateAmiPageNoshiInterface
{
    /**
     * 保存処理
     */
    public function execute(string|int $id, array $conditions);

}
