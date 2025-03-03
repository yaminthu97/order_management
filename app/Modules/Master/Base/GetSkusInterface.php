<?php

namespace App\Modules\Master\Base;

interface GetSkusInterface
{
    /**
     * 取得処理
     */
    public function execute($key, $itemId);
}
