<?php

namespace App\Modules\Order\Base;

interface GetTemplateFilePathInterface
{
    /**
     * get template file path
     */
    public function execute(string $accountCode, array $fileData);
}
