<?php

namespace App\Modules\Common\Base;

interface SearchPrefecturalInterface
{
    /**
     * 都道府県検索処理
     * @param int $invoiceSystemId 請求システムID
     */
    public function execute(array $condtions = [], array $options = []);
}
