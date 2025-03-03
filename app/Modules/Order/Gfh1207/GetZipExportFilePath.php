<?php

namespace App\Modules\Order\Gfh1207;

use App\Modules\Order\Base\GetZipExportFilePathInterface;

/**
 * get file path to save on S3 server module
 */
class GetZipExportFilePath implements GetZipExportFilePathInterface
{
    /**
     * get file path to save on S3 server
     * @return string (zip export file path)
     */
    public function execute($accountCode, $batchType, $batchExecutionId)
    {
        return $accountCode . "/zip/export/" . $batchType . "/" . $batchExecutionId . '.zip';
    }
}
