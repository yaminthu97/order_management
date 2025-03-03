<?php

namespace App\Modules\Order\Gfh1207;

use App\Modules\Order\Base\GetTextExportFilePathInterface;

class GetTextExportFilePath implements GetTextExportFilePathInterface
{
    /**
     * get the excel text file path
     */
    public function execute($accountCode, $batchType, $bathID)
    {
        return $accountCode . '/text/export/' . $batchType . '/' . $bathID . '.txt';
    }
}
