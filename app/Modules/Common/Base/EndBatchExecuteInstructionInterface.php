<?php

namespace App\Modules\Common\Base;

use App\Models\Common\Base\ExecuteBatchInstructionModel;
interface EndBatchExecuteInstructionInterface
{
    /**
     * バッチ実行指示、JOB終了処理
     * @param ExecuteBatchInstructionModel $batchExecute
     * @param array|null $options
     */
    public function execute(ExecuteBatchInstructionModel $batchExecute, ?array $options = []);
}
