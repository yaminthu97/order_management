<?php

namespace App\Modules\Master\Base;

interface SearchEcsInterface
{
    /**
     * 拡張データ取得処理
     */
    public function execute(array $conditions = [], array $options = []);
}
