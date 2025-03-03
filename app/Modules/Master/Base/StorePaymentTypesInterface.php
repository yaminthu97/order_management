<?php

namespace App\Modules\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 支払方法マスタ機能の登録処理インターフェース
 */
interface StorePaymentTypesInterface
{
    /**
     * 保存処理
     * @param array $fillData 登録データ
     * @param array $exFillData fillableに設定されていないデータ
     */
    public function execute(array $fillData, array $exFillData): Model;
}
