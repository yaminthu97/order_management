<?php

namespace App\Modules\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 項目名称マスタの新規作成処理インターフェース
 * モジュールのインターフェースは、executeメソッドのみを持つ
 * 新規作成処理の場合は、Newをプレフィックスとする。
 * **DBへの保存を行うStoreと混同しないように注意すること。**
 */
interface NewItemnameTypeInterface
{
    /**
     * 新規モデル作成処理
     * モデルインスタンスの作成のみ。****保存はしない。****
     * 入力配列でfillしたモデルを返す。必要に応じてfillできないデータを設定する。
     * fillable外の値が$fillDataに含まれる場合、Model::fill()で例外が発生する。
     * @param array $fillData 登録データ
     * @param array $exFillData fillableに設定されていないデータ
     * @return Model 登録結果(原則としてEloquentのモデルを返す)
     * @throws \App\Exceptions\ModuleValidationException バリデーションエラー時
     */
    public function execute(array $fillData = [], array $exFillData = []): Model;
}
