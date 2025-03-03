<?php

namespace App\Modules\Master\Base;

use App\Models\Master\Gfh1207\ItemnameTypeModel;
use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\Builder;

class SearchItemnameType implements SearchItemnameTypeInterface
{
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
        'm_itemname_type' => 'asc',
        'm_itemname_type_sort' => 'asc',
    ];

    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    /**
     * 表示名(フォーマット用)
     */
    protected $selectionValue = 'm_itemname_type_name';

    /**
     * 値(フォーマット用)
     */
    protected $selectionKey = 'm_itemname_types_id';

    public function __construct(EsmSessionManager $esmSessionManager)
    {
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute(array $conditions = [], array $options = [])
    {
        $query = ItemnameTypeModel::query();

        $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

        // 補足情報から追加で検索条件を設定する
        $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

        // ペジネーション設定
        if (isset($options['should_paginate']) && $options['should_paginate'] === true) {
            if (isset($options['limit'])) {
                return $query->paginate($options['limit'], ['*'], 'page', $options['page'] ?? 1);
            } else {
                return $query->paginate(config('esm.default_page_size.master'));
            }
        } elseif (isset($options['should_selection']) && $options['should_selection'] === true) {
            // $options['selection_key'] => $options['selection_value'] の形式で返す
            $selectionKey = $options['selection_key'] ?? $this->selectionKey;
            $selectionValue = $options['selection_value'] ?? $this->selectionValue;
            return $query->pluck($selectionValue, $selectionKey);
        } else {
            return $query->get();
        }
    }

    /**
     * 検索条件を組み上げる
     */
    public function setConditions($query, $conditions): Builder
    {
        // 検索条件を組み上げる
        if (isset($conditions['delete_flg'])) {
            $query->whereIn('delete_flg', $conditions['delete_flg']);
        }
        // m_account_id
        if (isset($conditions['m_account_id'])) {
            $query->where('m_account_id', $conditions['m_account_id']);
        }

        // m_itemname_type
        if (isset($conditions['m_itemname_type'])) {
            $query->whereIn('m_itemname_type', $conditions['m_itemname_type']);
        }

        if (isset($conditions['m_itemname_type_name_fuzzy_search_flg']) && $conditions['m_itemname_type_name_fuzzy_search_flg'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['m_itemname_type_name']) && strlen($conditions['m_itemname_type_name']) > 0) {
                $query->where('m_itemname_type_name', 'like', "%{$conditions['m_itemname_type_name']}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['m_itemname_type_name']) && strlen($conditions['m_itemname_type_name']) > 0) {
                $query->where('m_itemname_type_name', 'like', "{$conditions['m_itemname_type_name']}%");
            }
        }

        return $query;
    }

    /**
     * 補足情報から追加で検索条件を設定する
     */
    public function setOptions($query, $options): Builder
    {
        // orderby
        if (isset($options['sorts'])) {
            if (is_array($options['sorts'])) {
                foreach ($options['sorts'] as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            } else {
                $query->orderBy($options['sorts'], 'asc');
            }
        } else {
            foreach ($this->defaultSorts as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }
}
