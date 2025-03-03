<?php

namespace App\Modules\Payment\Base;

use App\Modules\Payment\Base\GetCsvZipExportFilePathInterface;

/**
 * get file path to save on S3 server module
 */
class GetCsvZipExportFilePath implements GetCsvZipExportFilePathInterface
{
    /**
     * get file path to save on S3 server
     * @return string (zip file path)
     */
    public function execute($accountCode, $batchType, $batchExecutionId)
    {
        return $accountCode . "/csv/export/" . $batchType . "/" . $batchExecutionId . '.zip';
    }
}
