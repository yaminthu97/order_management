<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleFailed;
use App\Models\Master\Gfh1207\PaymentTypeModel;
use App\Modules\Master\Base\SearchPaymentTypesInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class SearchPaymentTypes implements SearchPaymentTypesInterface
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
    protected $defaultConditions = [];

    /**
     * デフォルトの検索オプション
     */
    protected $defaultOptions = [];

    /**
     * デフォルトのソート条件
     */
    protected $defaultSorts = [
        'm_payment_types_sort' => 'asc'
    ];

    public function execute(array $conditions = [], array $options = [])
    {
        try {
            $query = PaymentTypeModel::query();

            $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

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
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        } catch (\Exception $e) {
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
        } else {
            $query->where('delete_flg', 0);
        }

        // 支払方法種類
        if (isset($conditions['payment_type']) && sizeof($conditions['payment_type']) > 0) {
            $query->whereIn('payment_type', $conditions['payment_type']);
        }

        // 支払方法名
        if (isset($conditions['m_payment_types_name_fuzzy_search_flg']) && $conditions['m_payment_types_name_fuzzy_search_flg'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['m_payment_types_name']) && strlen($conditions['m_payment_types_name']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['m_payment_types_name']);
                $query->where('m_payment_types_name', 'like', "%{$str}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['m_payment_types_name']) && strlen($conditions['m_payment_types_name']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['m_payment_types_name']);
                $query->where('m_payment_types_name', 'like', "{$str}%");
            }
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
