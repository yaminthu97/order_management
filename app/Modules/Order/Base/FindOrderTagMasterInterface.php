<?php

namespace App\Modules\Order\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 受注タグマスタの取得処理インターフェース
 * モジュールのインターフェースは、executeメソッドのみを持つ
 * 単一のデータを返す場合は、Findをプレフィックスとする。
 */
interface FindOrderTagMasterInterface
{
    /**
     * 取得処理
     * @param string|int $id 取得対象のID
     * @return Model 検索結果(原則としてEloquentのモデルを返す)
     * @throws ModelNotFoundException データが見つからなかった場合
     */
    public function execute(string|int $id): Model;
}
