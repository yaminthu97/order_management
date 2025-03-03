<?php

namespace App\Modules\Customer\Base;

interface SearchDestinationsInterface
{
    /**
     * 検索処理
     */
    public function execute(array $condtions=[], array $options=[]);
}
