<?php

namespace App\Modules\Order\Base;

interface CheckOperatorAuthInterface
{
    public function execute($menuType);
}
