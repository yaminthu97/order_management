<?php

namespace App\Modules\Payment\Base;

use App\Models\Claim\Gfh1207\ReceiptOutputModel;

/**
 * 領収書データ作成インターフェース
 */
interface CreateReceiptDataInterface
{
    /**
     * 領収書データ作成
     *
     */
    public function execute($receiptOutputId,$batchExecute);
}
