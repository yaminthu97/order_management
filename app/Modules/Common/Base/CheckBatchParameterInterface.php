<?php

namespace App\Modules\Common\Base;

interface CheckBatchParameterInterface
{
    /**
     * check batch parameter
     */
    public function execute(string $jsonData, array $checkArray);
}
