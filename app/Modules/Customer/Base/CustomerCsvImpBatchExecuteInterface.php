<?php

namespace App\Modules\Customer\Base;

use Illuminate\Http\Request;

interface CustomerCsvImpBatchExecuteInterface
{
    /**
     * 検索処理
     */
    public function execute(Request $request, string $batchType): string;
}
