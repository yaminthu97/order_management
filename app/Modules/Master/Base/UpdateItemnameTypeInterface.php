<?php

namespace App\Modules\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 項目名称マスタの更新処理インターフェース
 * モジュールのインターフェースは、executeメソッドのみを持つ
 * 更新処理の場合は、Updateをプレフィックスとする。
 * StoreかUpdateかは、主テーブルの新規登録か更新かで判断する。例えば、受注編集によって送付先や明細に新規登録が発生する場合、主は受注なのでUpdateとなる。
 */
interface UpdateItemnameTypeInterface
{
    /**
     * 更新処理
     * @param string|int $id 更新対象のID
     * @param array $fillData 更新データ
     * @param array $exFillData fillableに設定されていないデータ
     * @return Model 更新結果(原則としてEloquentのモデルを返す)
     * @throws \App\Exceptions\ModuleValidationException バリデーションエラー時
     */
    public function execute(string|int $id, array $fillData, array $exFillData): Model;
}
