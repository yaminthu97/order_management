<?php

namespace App\Modules\Common\Base;

interface SearchNoshiFormatInterface
{
    /**
     * 検索処理
     */
    public function execute(array $condtions=[], array $options=[]);
}
