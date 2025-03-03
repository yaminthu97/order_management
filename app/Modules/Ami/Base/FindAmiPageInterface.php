<?php

namespace App\Modules\Ami\Base;

interface FindAmiPageInterface
{
    /**
     * 検索処理
     */
    public function execute(int $id);
}
