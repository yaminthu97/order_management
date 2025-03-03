<?php

namespace App\Modules\Order\Base;

interface RegisterOrderDrawingInterface
{
    /**
     * 在庫引当API
     */
    public function execute(array $params);
}
