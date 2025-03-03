<?php

namespace App\Modules\Order\Base;

interface RegisterOrderProgressInterface
{
    /**
     * 進捗区分変更API
     */
    public function execute(array $params);
}
