<?php

namespace App\Modules\Master\Base;

interface GetEcsDetailInterface
{
    /**
     * 拡張データ取得処理
     */
    public function execute($key);
}
