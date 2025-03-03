<?php

namespace App\Modules\Master\Base;

interface UpdateShopGfhInterface
{
    /**
     * 保存処理
     */
    public function execute(int $id, array $conditions);

}
