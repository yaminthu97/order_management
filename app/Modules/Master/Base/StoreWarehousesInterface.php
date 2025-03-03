<?php

namespace App\Modules\Master\Base;

/**
 * 倉庫マスタ機能の登録処理インターフェース
 */
interface StoreWarehousesInterface
{
    /**
     * 保存処理
     * @param array $fillData 登録データ
     * @param array $exFillData fillableに設定されていないデータ
     */
    public function execute(array $fillData, array $exFillData);
}
