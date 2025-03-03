<?php

namespace App\Modules\Order\Base;

interface SetInputBatchExecuteInterface
{
    /**
     * 検索処理
     */
    public function execute($request, $submitName);
}
