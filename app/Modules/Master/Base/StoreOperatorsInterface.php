<?php

namespace App\Modules\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 社員マスタ機能の登録処理インターフェース
 */
interface StoreOperatorsInterface
{
    /**
     * 保存処理
     * @param array $fillData 登録データ
     * @param array $exFillData fillableに設定されていないデータ
     */
    public function execute(array $fillData, array $exFillData): Model;
}
