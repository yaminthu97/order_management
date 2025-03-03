<?php

namespace App\Modules\Ami\Base;

interface FindAmiSkuInterface
{
    /**
     * 検索処理
     */
    public function execute(int $id);
}
