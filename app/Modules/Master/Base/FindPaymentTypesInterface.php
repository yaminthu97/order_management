<?php

namespace App\Modules\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 支払方法マスタ検索インターフェイス
 */
interface FindPaymentTypesInterface
{
    /**
     * 取得処理
     * @param string|int $id 取得対象のID
     * @return Model 検索結果(原則としてEloquentのモデルを返す)
     * @throws ModelNotFoundException データが見つからなかった場合
     */
    public function execute(string|int $id): Model;
}
