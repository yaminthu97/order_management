<?php

namespace App\Modules\Order\Base;

interface GetTemplateFileNameInterface
{
    /**
     * get template file name
     */
    public function execute(string $fileName, int $accountId);
}
