<?php

namespace App\Modules\Customer\Base;

use Illuminate\Database\Eloquent\Model;

interface StoreCustCommunicationDtlInterface
{
    /**
     * 保存処理
     */
    public function execute(array $fillData): Model;
}
