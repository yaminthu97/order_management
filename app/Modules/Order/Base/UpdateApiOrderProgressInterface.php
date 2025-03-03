<?php

namespace App\Modules\Order\Base;

interface UpdateApiOrderProgressInterface
{
    /**
     * 拡張データ取得処理
     */
    public function execute(array $params);
}
