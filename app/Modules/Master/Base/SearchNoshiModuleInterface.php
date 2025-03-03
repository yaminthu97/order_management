<?php

namespace App\Modules\Master\Base;

interface SearchNoshiModuleInterface
{
    /**
     * 取得処理
     */
    public function execute(array $conditions = [], array $options = []);
}
