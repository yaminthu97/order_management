<?php

namespace App\Modules\Ami\Base;

interface SearchAmiPageNoshiInterface
{
    /**
     * 検索処理
     */
    public function execute(array $condtions=[], array $options=[]);
}
