<?php

namespace App\Modules\Customer\Base;

use Illuminate\Database\Eloquent\Model;

interface UpdateCustCommunicationInterface
{
    /**
     * 保存処理
     */
    public function execute(string|int $id, array $fillData, array $exFillData): Model;
}
