<?php

namespace App\Modules\Customer\Base;

interface SearchInterface
{
    /**
     * 検索処理
     */
    public function execute(array $conditions);
}
