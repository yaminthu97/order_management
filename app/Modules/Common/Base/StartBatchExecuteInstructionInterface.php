<?php

namespace App\Modules\Common\Base;

interface StartBatchExecuteInstructionInterface
{
    /**
     * バッチ実行指示、JOB開始処理
     * @param int $batchId
     * @param array|null $options
     */
    public function execute(?int $batchId, ?array $options = []);
}
