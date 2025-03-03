<?php

namespace App\Modules\Master\Base;

interface SearchItemnameTypeInterface
{
    /**
     * 拡張データ取得処理
     */
    public function execute(array $conditions = [], array $options = []);
}
