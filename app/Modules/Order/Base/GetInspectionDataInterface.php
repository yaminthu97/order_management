<?php

namespace App\Modules\Order\Base;

interface GetInspectionDataInterface
{
    public function execute($searchResult,$depositorNumber);
}
