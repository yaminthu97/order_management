<?php

namespace App\Modules\Order\Base;

interface SetCurrentApiKeyInterface
{
    /**
     * 検索処理
     */
    public function execute(array $conditions);
}
