<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleFailed;
use App\Models\Master\Gfh1207\OperatorModel;
use App\Modules\Master\Base\SearchOperatorsInterface;
use App\Services\EsmSessionManager;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class SearchOperators implements SearchOperatorsInterface
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
        'delete_flg' => [0]
    ];

    /**
     * デフォルトの検索オプション
     */
    protected $defaultOptions = [];

    /**
     * デフォルトのソート条件
     */
    protected $defaultSorts = [
        'm_operators_id' => 'asc'
    ];

    public function execute(array $conditions = [], array $options = [])
    {
        try {
            $query = OperatorModel::query();

            $query = $this->setConditions($query, array_merge(empty($conditions) ? $this->defaultConditions : [], $conditions));

            // 補足情報から追加で検索条件を設定する
            $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

            $count = $query->count();
            $page = ($count > config('esm.default_page_size.master')) ? ($options['page'] ?? 1) : 1;

            // ペジネーション設定
            if (isset($options['should_paginate']) && $options['should_paginate'] === true) {
                if (isset($options['limit'])) {
                    return $query->paginate($options['limit'], ['*'], 'page', $page);
                } else {
                    return $query->paginate(config('esm.default_page_size.master'));
                }
            } else {
                return $query->get();
            }
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        } catch (Exception $e) {
            ModuleFailed::dispatch(__CLASS__, ['conditions' => $conditions, 'options' => $options], $e);
            throw $e;
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

        // 使用区分
        if (isset($conditions['delete_flg'])) {
            $query->whereIn('delete_flg', $conditions['delete_flg']);
        }

        // 社員ID
        if (isset($conditions['m_operators_id']) && strlen($conditions['m_operators_id']) > 0) {
            $query->where('m_operators_id', '=', $conditions['m_operators_id']);
        }

        // 社員名
        if (isset($conditions['m_operator_name_fuzzy_search_flg']) && $conditions['m_operator_name_fuzzy_search_flg'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['m_operator_name']) && strlen($conditions['m_operator_name']) > 0) {
                $str = str_replace([" ", " "], "", $conditions['m_operator_name']);
                $query->where('m_operator_name', 'like', "%{$str}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['m_operator_name']) && strlen($conditions['m_operator_name']) > 0) {
                $str = str_replace([" ", " "], "", $conditions['m_operator_name']);
                $query->where('m_operator_name', 'like', "{$str}%");
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
