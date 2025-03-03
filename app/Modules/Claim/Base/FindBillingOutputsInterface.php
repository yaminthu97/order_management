<?php

namespace App\Modules\Claim\Base;

/**
 * 請求書出力履歴取得インターフェース
 */
interface FindBillingOutputsInterface
{
    /**
     * 請求書出力履歴取得
     *
     */
    public function execute(int $id);
}
