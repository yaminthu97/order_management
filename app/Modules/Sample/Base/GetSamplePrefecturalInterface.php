<?php

namespace App\Modules\Sample\Base;

use Illuminate\Database\Eloquent\Collection;

/**
 * サンプル機能の都道府県取得処理インターフェース
 * モジュールのインターフェースは、executeメソッドのみを持つ
 * 検索条件が不要であり、複数件を返す余地があり、常に全件を取得する場合は、Getをプレフィックスとする。
 * またexecuteメソッドの返り値は、原則としてEloquentのコレクションを返す(Eloquentに依存しない場合は、Illuminate\Support\Collectionを返す)
 */
interface GetSamplePrefecturalInterface
{
    /**
     * 検索処理
     * 条件の指定が不要であり、常に全件を取得する場合は、引数は不要
     * @return Collection 検索結果(原則としてEloquentのコレクションを返す)
     */
    public function execute(): Collection;
}
