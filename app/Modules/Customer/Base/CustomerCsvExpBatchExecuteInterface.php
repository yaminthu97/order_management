<?php

namespace App\Modules\Customer\Base;

use Illuminate\Http\Request;

interface CustomerCsvExpBatchExecuteInterface
{
    /**
     * 検索処理
     */
    public function execute(Request $requestData, string $submitName, array $viewExtendData): string;
}
