<?php

namespace App\Modules\Order\Base;

interface SearchInterface
{
    /**
     * 検索処理
     */
    public function execute(array $conditions = [], array $options = []);
}
