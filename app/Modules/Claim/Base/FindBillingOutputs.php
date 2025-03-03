<?php

namespace App\Modules\Claim\Base;

use App\Models\Claim\Gfh1207\BillingOutputModel;
use App\Modules\Claim\Base\FindBillingOutputsInterface;

class FindBillingOutputs implements FindBillingOutputsInterface
{
    /**
     * 出荷情報取得
     *
     * @param int $id 請求書出力履歴ID
     */
    public function execute(int $id)
    {
        $query = BillingOutputModel::query();
        return $query->find($id);
    }
}