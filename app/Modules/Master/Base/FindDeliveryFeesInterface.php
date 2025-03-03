<?php

namespace App\Modules\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 配送別送料マスタ検索インターフェイス
 */
interface FindDeliveryFeesInterface
{
    /**
     * 取得処理
     * @param string|int $id 取得対象のID
     */
    public function execute(string|int $id);
}
