<?php

namespace App\Modules\Master\Base;
/**
 * 配送別送料マスタ機能の更新処理インターフェース
 */
interface UpdateDeliveryFeesInterface
{
    /**
     * 更新処理
     * @param string|int $id 更新対象のID
     * @param array $fillData 更新データ
     * @param array $exFillData fillableに設定されていないデータ
     */
    public function execute(string|int $id, array $fillData, array $exFillData): bool;
}
