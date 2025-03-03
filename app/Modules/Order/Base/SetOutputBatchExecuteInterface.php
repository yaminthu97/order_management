<?php

namespace App\Modules\Order\Base;

interface SetOutputBatchExecuteInterface
{
    /**
     * 出力系バッチの処理
     */
    public function execute($req, $paginator, $submitType);
}
