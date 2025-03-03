<?php

namespace App\Modules\Common\Base;

use App\Models\Common\Base\DeliveryCompanyTimeHopeModel;
use Illuminate\Database\Eloquent\Builder;

class SearchDeliveryCompanyTimeHope implements SearchDeliveryCompanyTimeHopeInterface
{
    /**
     * デフォルトの検索条件
     */
    protected $defaultConditions = [
        'with_deleted' => '0',
        'delete_flg' => '0'
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
        'm_delivery_time_hope_id' => 'asc'
    ];

    public function execute(array $conditions = [], array $options = [])
    {
        $query = DeliveryCompanyTimeHopeModel::query();

        $query->with('deliveryCompany');

        $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

        // 補足情報から追加で検索条件を設定する
        $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

        // ペジネーション設定
        if(isset($options['should_paginate']) && $options['should_paginate'] === true) {
            if(isset($options['limit'])) {
                return $query->paginate($options['limit']);
            } else {
                return $query->paginate(config('esm.default_page_size.master'));
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
        // 検索条件を組み上げる

        // 倉庫種別の指定がなければデフォルト1を設定
        if(isset($conditions['m_warehouse_type'])){
            if ($conditions['m_warehouse_type'] !== 'all') {
                $query->where('m_warehouse_type', $conditions['m_warehouse_type']);
            }
        } else {
            $query->where('m_warehouse_type', 1);
        }

        // m_delivery_time_hope_id と m_delivery_company_id が同一物がある場合、invoice_system_cd が最大の物だけを取得する
        if(isset($conditions['invoice_system_cd'])){ 
            $query->where('invoice_system_cd', $conditions['invoice_system_cd']);
        } else {
            $query->whereIn('m_delivery_company_time_hope_id', function ($subQuery) {
                $subQuery->select('m_delivery_company_time_hope_id')
                    ->from('m_delivery_company_time_hope as t1')
                    ->whereRaw('invoice_system_cd = (
                        SELECT MAX(t2.invoice_system_cd)
                        FROM m_delivery_company_time_hope as t2
                        WHERE t2.m_delivery_time_hope_id = t1.m_delivery_time_hope_id
                        AND t2.m_delivery_company_id = t1.m_delivery_company_id
                    )');
            });
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
