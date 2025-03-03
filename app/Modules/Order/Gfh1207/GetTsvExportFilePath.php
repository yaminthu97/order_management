<?php

namespace App\Modules\Order\Gfh1207;

use App\Modules\Order\Base\GetTsvExportFilePathInterface;

/**
 * get file path to save on S3 server module
 */
class GetTsvExportFilePath implements GetTsvExportFilePathInterface
{
    /**
     * get file path to save on S3 server
     * @return string (tsv export file path)
     */
    public function execute($accountCode, $batchType, $batchExecutionId)
    {
        return $accountCode . "/tsv/export/" . $batchType . "/" . $batchExecutionId . '.tsv';
    }
}
