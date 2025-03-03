<?php

namespace App\Modules\Master\Base;

interface UpdateNoshiModuleInterface
{
    /**
     * 取得処理
     */
    public function execute( $id, array $params );
}
