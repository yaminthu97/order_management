<?php

namespace App\Modules\Order\Gfh1207;

use App\Modules\Order\Base\GetCsvExportFilePathInterface;

/**
 * get file path to save on S3 server module
 */
class GetCsvExportFilePath implements GetCsvExportFilePathInterface
{
    /**
     * get file path to save on S3 server
     * @return string (csv export file path)
     */
    public function execute($accountCode, $batchType, $fileName)
    {
        return $accountCode . "/csv/export/" . $batchType . "/" . $fileName . '.csv';
    }
}
