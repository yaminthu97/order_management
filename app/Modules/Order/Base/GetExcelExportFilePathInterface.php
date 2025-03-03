<?php

namespace App\Modules\Order\Base;

interface GetExcelExportFilePathInterface
{
    /**
     * get the excel export file path
     */
    public function execute(string $accountCode, string $batchType, int|string $batchID);
}
