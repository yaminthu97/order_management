<?php

namespace App\Modules\Common\Base;

interface SearchNoshiNamingPatternInterface
{
    /**
     * 検索処理
     */
    public function execute(array $condtions=[], array $options=[]);
}
