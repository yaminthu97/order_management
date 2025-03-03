<?php

namespace App\Modules\Order\Gfh1207;

use App\Modules\Order\Base\GetExcelExportFilePathInterface;

class GetExcelExportFilePath implements GetExcelExportFilePathInterface
{
    /**
     * get the excel export file path
     */
    public function execute(string $accountCode, string $batchType, int|string $batchID)
    {
        return $accountCode . '/excel/export/' . $batchType . '/' . $batchID . '.xlsx';
    }
}
