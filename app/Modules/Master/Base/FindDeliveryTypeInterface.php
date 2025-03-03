<?php

namespace App\Modules\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 配送方法機能の取得処理インターフェース
 * モジュールのインターフェースは、executeメソッドのみを持つ
 * 単一のデータを返す場合は、Findをプレフィックスとする。
 */
interface FindDeliveryTypeInterface
{
    /**
     * 取得処理
     * @param string|int $id 取得対象のID
     * @return Model 検索結果(原則としてEloquentのモデルを返す)
     * @throws ModelNotFoundException データが見つからなかった場合
     */
    public function execute(string|int $id, array $option): Model;
}
