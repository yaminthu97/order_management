<?php
namespace App\Modules\Sample\Base;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * サンプル機能の検索処理インターフェース
 * モジュールのインターフェースは、executeメソッドのみを持つ
 * 検索条件や検索オプションが必要な場合は、Searchをプレフィックスとする。
 */
interface SearchSampleInterface
{

    /**
     * 検索処理
     * @param array $conditions 検索条件(Where句に係る条件)
     * @param array $options 検索オプション(ソート条件やセレクト句、リレーション関係など)
     * @return Collection 検索結果(原則としてEloquentのコレクションかペジネータを返す)
     */
    public function execute(array $conditions, array $options):Collection|LengthAwarePaginator;
}
