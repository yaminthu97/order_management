<?php

namespace App\Modules\Order\Base;

/**
 * get file path to save on S3 server interface
 */
interface GetCsvExportFilePathInterface
{
    /**
     * get file path to save on S3 server
     *
     * @param string
     */

    public function execute($accountCode, $batchType, $fileName);
}
