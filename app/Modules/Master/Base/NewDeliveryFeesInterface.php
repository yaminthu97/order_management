<?php

namespace App\Modules\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 配送別送料マスタ機能の新規作成処理インターフェース
 */
interface NewDeliveryFeesInterface
{
    /**
     * @param array $fillData 登録データ
     * @param array $exFillData fillableに設定されていないデータ
     * @return Model 登録結果(原則としてEloquentのモデルを返す)
     */
    public function execute(array $fillData = [], array $exFillData = []): Model;
}
