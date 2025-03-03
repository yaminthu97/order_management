<?php

namespace App\Modules\Order\Gfh1207;

use App\Models\Master\Gfh1207\OrderTagModel;
use App\Modules\Order\Base\SearchOrderTagMasterModuleInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

class SearchOrderTagMasterModule implements SearchOrderTagMasterModuleInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

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
        'm_order_tag_sort' => 'asc'
    ];

    public function __construct(
        EsmSessionManager $esmSessionManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    /**
     * 検索条件を組み上げる
     */
    public function execute(array $conditions = [], array $options = [])
    {
        $query = OrderTagModel::query();

        $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

        // 補足情報から追加で検索条件を設定する
        $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

        // ペジネーション設定
        if (isset($options['should_paginate']) && $options['should_paginate'] === true) {
            if (isset($options['page'])) {
                Paginator::currentPageResolver(function () use ($options) {
                    return $options['page'];
                });
            }
            if (isset($options['limit'])) {
                return $query->paginate($options['limit']);
            } else {
                return $query->paginate(config('esm.default_page_size.order'));
            }
        } else {
            return $query->get();
        }

    }

    /**
     * 検索条件を組み上げる
     */
    public function setConditions($query, $conditions): Builder
    {
        // m_account_id
        if (isset($conditions['m_account_id'])) {
            $query->where('m_account_id', $conditions['m_account_id']);
        }

        // 受注タグ名称   
        if (isset($conditions['tag_name']) && strlen($conditions['tag_name']) > 0 )
        {
            $query->where('tag_name', 'like', "%{$conditions['tag_name']}%");
        }

        // 自動付与タイミング    
        if (isset($conditions['auto_timming']) && strlen($conditions['auto_timming']) > 0 )
        {
            $query->where('auto_timming', $conditions['auto_timming']);
        }

        // 進捗停止区分
        if (isset($conditions['deli_stop_flg']) && strlen($conditions['deli_stop_flg']) > 0 )
        {
            $query->where('deli_stop_flg', $conditions['deli_stop_flg']);
        }

        // 説明文
        if (isset($conditions['tag_context']) && strlen($conditions['tag_context']) > 0 )
        {
            $query->where('tag_context', 'like', "%{$conditions['tag_context']}%");
        }

        return $query;
    }

    /**
     * 補足情報から追加で検索条件を設定する
     */
    public function setOptions($query, $options): Builder
    {
        // 補足情報から追加で検索条件を設定する
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
