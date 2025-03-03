<?php

namespace App\Modules\Master\Base;

/**
 * 配送別送料マスタ機能の登録処理インターフェース
 */
interface StoreDeliveryFeesInterface
{
    /**
     * 保存処理
     * @param array $fillData 登録データ
     */
    public function execute(array $fillData): bool;
}
