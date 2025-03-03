<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Models\Master\Gfh1207\DeliveryTypeModel;
use App\Modules\Master\Base\SearchDeliveryTypeInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SearchDeliveryType implements SearchDeliveryTypeInterface
{
    /**
     * デフォルトの検索条件
     */
    protected $defaultConditions = [
        'm_account_id' => 0
    ];

    /**
     * デフォルトの検索オプション
     */
    protected $defaultOptions = [];

    /**
     * デフォルトのソート条件
     */
    protected $defaultSorts = [
        'm_delivery_sort' => 'asc',
    ];

    public function __construct(protected EsmSessionManager $EsmSessionManager)
    {
        $this->defaultConditions['m_account_id'] = $EsmSessionManager->getAccountId();

        if (($this->defaultConditions['m_account_id']) === 0) {
            // 企業アカウントIDが指定されていない場合は、エラー
            throw new InvalidArgumentException('企業アカウントIDが指定されていません');
        }
    }

    public function execute(array $conditions = [], array $options = [])
    {

        try {

            $query = DeliveryTypeModel::query();

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

            } else {
                return $query->get();
            }

        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, ['conditions' => $conditions, 'options' => $options], $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, $result->toArray());
        return $result;
    }

    /**
     * 検索条件を組み上げる
     */
    public function setConditions($query, $conditions): Builder
    {
        // 検索条件を組み上げる
        // m_account_id
        if (isset($conditions['m_account_id'])) {
            $query->where('m_account_id', $conditions['m_account_id']);
        }

        // 使用区分
        if (isset($conditions['m_delivery_types_id']) && strlen($conditions['m_delivery_types_id']) > 0) {
            $query->where('m_delivery_types_id', $conditions['m_delivery_types_id']);
        }

        // 使用区分
        if (isset($conditions['delete_flg']) && sizeof($conditions['delete_flg']) > 0) {
            $query->whereIn('delete_flg', $conditions['delete_flg']);
        }

        // 配送方法
        if (isset($conditions['delivery_type']) && sizeof($conditions['delivery_type']) > 0) {
            $query->whereIn('delivery_type', $conditions['delivery_type']);
        }

        // 配送方法名
        if (isset($conditions['m_delivery_type_name_fuzzy_search_flg']) && $conditions['m_delivery_type_name_fuzzy_search_flg'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['m_delivery_type_name']) && strlen($conditions['m_delivery_type_name']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['m_delivery_type_name']);
                $query->where('m_delivery_type_name', 'like', "%{$str}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['m_delivery_type_name']) && strlen($conditions['m_delivery_type_name']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['m_delivery_type_name']);
                $query->where('m_delivery_type_name', 'like', "{$str}%");
            }
        }

        return $query;
    }

    /**
     * 補足情報から追加で検索条件を設定する
     */
    public function setOptions($query, $options): Builder
    {
        // リレーションのeagerload指定
        if (isset($options['with'])) {
            $query->with($options['with']);
        }

        // orderby
        if (isset($options['sorts'])) {
            if (is_array($options['sorts'])) {
                Log::debug('sorts', $options['sorts']);
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
