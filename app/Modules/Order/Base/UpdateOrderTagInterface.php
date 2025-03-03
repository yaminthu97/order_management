<?php

namespace App\Modules\Order\Base;

interface UpdateOrderTagInterface
{
    public function execute(int $orderHdrId, int $orderTagId, array $params);
}
