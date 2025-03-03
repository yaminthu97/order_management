<?php

namespace App\Modules\Customer\Base;

interface SearchCustCommunicationInterface
{
    /**
     * 検索処理
     */
    public function execute(array $conditions = [], array $options = []);
}
