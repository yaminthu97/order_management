<?php
namespace App\Modules\Master\Gfh1207;

use Illuminate\Support\Facades\Log;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Warehouse\Base\WarehouseModel;
use App\Modules\Master\Base\SearchWarehousesInterface;

class SearchWarehouses implements SearchWarehousesInterface
{
    /**
    * ESMセッション管理クラス
    */
    protected $esmSessionManager;

    public function __construct(
        EsmSessionManager $esmSessionManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    /**
     * デフォルトの検索条件
     */
    protected $defaultConditions = [
    ];

    /**
     * デフォルトの検索オプション
     */
    protected $defaultOptions = [
    ];

    /**
     * デフォルトのソート条件
     */
    protected $defaultSorts = [
        'm_warehouse_sort' => 'asc'
    ];

    public function execute(array $conditions = [], array $options = [])
    {
        try {
            // 検索処理
            $query = WarehouseModel::query();

            $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

            // 補足情報から追加で検索条件を設定する
            $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

            // ペジネーション設定
            if(isset($options['should_paginate']) && $options['should_paginate'] === true) {
                if(isset($options['limit'])) {
                    return $query->paginate($options['limit'], ['*'], 'page', $options['page']);
                } else {
                    return $query->paginate(config('esm.default_page_size.master'));
                }
            } else {
                return $query->get();
            }
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }


    /**
     * 検索条件を組み上げる
     */
    public function setConditions($query, $conditions): Builder
    {
        // 検索条件を組み上げる
        $accountId = $this->esmSessionManager->getAccountId();
        $query->where('m_account_id', $accountId);

        // 倉庫名
        if (isset($conditions['m_warehouse_name_fuzzy_search_flg']) && $conditions['m_warehouse_name_fuzzy_search_flg'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['m_warehouse_name']) && strlen($conditions['m_warehouse_name']) > 0) {
                $query->where('m_warehouse_name', 'like', "%{$conditions['m_warehouse_name']}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['m_warehouse_name']) && strlen($conditions['m_warehouse_name']) > 0) {
                $query->where('m_warehouse_name', 'like', "{$conditions['m_warehouse_name']}%");
            }
        }

        // 使用区分
        if (isset($conditions['delete_flg'])) {
            $query->whereIn('delete_flg', $conditions['delete_flg']);
        } else {
            $query->where('delete_flg', 0);
        }

        // 引当倉庫有効フラグ
        if (isset($conditions['priority_flg'])) {
            $query->whereIn('m_warehouse_priority_flg', $conditions['priority_flg']);
        }

        return $query;
    }

    /**
     * 補足情報から追加で検索条件を設定する
     */
    public function setOptions($query, $options): Builder
    {
        // 補足情報から追加で検索条件を設定する

        /**
         * @todo カラム指定
         */
        // if(isset($options['columns'])){
        //     $query->select($options['columns']);
        // }

        /**
         * @todo リレーションのeagerload指定
         * 適宜条件も含める
         */
        // if(isset($options['with'])){
        //     $query->with($options['with']);
        // }

        // orderby
        if(isset($options['sorts'])) {
            if(is_array($options['sorts'])) {
                foreach($options['sorts'] as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            } else {
                $query->orderBy($options['sorts'], 'asc');
            }
        } else {
            foreach($this->defaultSorts as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }
}
