<?php

namespace App\Modules\Master\Base;

interface GetItemnameTypeInterface
{
    public function execute($itemType, $deleteFlag = 0, $orderBy = null);
}
