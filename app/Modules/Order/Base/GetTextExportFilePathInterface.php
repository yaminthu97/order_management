<?php

namespace App\Modules\Order\Base;

interface GetTextExportFilePathInterface
{
    /**
     * get the text export file path
     */
    public function execute(string $accountCode, string $batchType, int $bathID);
}
