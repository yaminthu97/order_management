<?php

namespace App\Modules\Order\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 受注タグマスタの確認処理インターフェース
 * モジュールのインターフェースは、executeメソッドのみを持つ
 * データの確認処理を行う場合は、Notifyをプレフィックスとする。
 * 入力されたデータをモデルに詰めて返す。
 * DBへの保存は行わないため、StoreやUpdateと混同しないように注意すること。
 */
interface NotifyOrderTagMasterInterface
{
    /**
     * 受注タグ確認処理
     * 入力配列でfillしたモデルを返す。****保存はしない。****
     * 主キーでfindOrNewするのが基本。必要に応じてfillできないデータを設定する。
     * fillable外の値が$fillDataに含まれる場合、Model::fill()で例外が発生する。
     * 親子データを扱う場合、setRelation()を使うことを検討する(参考：https://qiita.com/sgrs38/items/d8f5b5d8a04e74ab89a1)
     * @param array $fillData これまでに入力されたデータ
     * @param array $exFillData fillableに設定されていないデータ
     * @param int|string|null $id 更新対象のID。 新規の場合はnull
     * @return Model
     */
    public function execute(array $fillData, array $exFillData, int|string|null $id): Model;
}
