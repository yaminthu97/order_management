<?php

namespace App\Modules\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 配送リードタイムマスタ機能の確認処理インターフェース
 */
interface NotifyDeliveryReadtimeInterface
{
    /**
     * @param array $fillData これまでに入力されたデータ
     * @param array $exFillData fillableに設定されていないデータ
     * @param int|string|null $id 更新対象のID。 新規の場合はnull
     * @return Model
     */
    public function execute(array $fillData, array $exFillData, int|string|null $id): Model;
}
