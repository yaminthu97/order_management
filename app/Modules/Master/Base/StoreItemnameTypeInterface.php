<?php

namespace App\Modules\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 項目名称マスタの登録処理インターフェース
 * モジュールのインターフェースは、executeメソッドのみを持つ
 * 登録処理の場合は、Storeをプレフィックスとする。
 * StoreかUpdateかは、主テーブルの新規登録か更新かで判断する。例えば、受注編集によって送付先や明細に新規登録が発生する場合、主は受注なのでUpdateとなる。
 */
interface StoreItemnameTypeInterface
{
    /**
     * 保存処理
     * @param array $fillData 登録データ
     * @param array $exFillData fillableに設定されていないデータ
     * @return Model 登録結果(原則としてEloquentのモデルを返す)
     * @throws \App\Exceptions\ModuleValidationException バリデーションエラー時
     */
    public function execute(array $fillData, array $exFillData): Model;
}
