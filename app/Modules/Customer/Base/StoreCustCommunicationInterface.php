<?php

namespace App\Modules\Customer\Base;

interface StoreCustCommunicationInterface
{
    /**
     * 保存処理
     */
    public function execute(array $fillData, array $exFillData);
}
