<?php

namespace App\Modules\Customer\Base;

interface SetCustHistOutputBatchExecuteInterface
{
    /**
     * 出力系バッチの処理
     */
    public function execute($req);
}
