<?php

namespace App\Modules\Order\Base;

interface GetTemplateDataInterface
{
    /**
     * get template data
     */
    public function execute(array $idData);
}
