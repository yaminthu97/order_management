<?php

namespace App\Modules\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * 社員マスタ機能の更新処理インターフェース
 */
interface UpdateOperatorsInterface
{
    /**
     * 更新処理
     * @param int $id 更新対象のID
     * @param array $fillData 更新データ
     * @param array $exFillData fillableに設定されていないデータ
     * @return Model 更新結果(原則としてEloquentのモデルを返す)
     * @throws \App\Exceptions\ModuleValidationException バリデーションエラー時
     */
    public function execute(int $id, array $fillData, array $exFillData): Model;
}
