<?php

namespace App\Modules\Common\Base;

interface RegisterBatchExecuteInstructionInterface
{
    /**
     * バッチ実行指示登録
     */
    public function execute(array $params);
}
