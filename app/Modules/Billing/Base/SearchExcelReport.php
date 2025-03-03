<?php
namespace App\Modules\Billing\Base;

use App\Models\Order\Base\OrderDestinationModel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

use InvalidArgumentException;

class SearchExcelReport implements SearchExcelReportInterface
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
        'entry_timestamp' => 'desc'
    ];

    /**
     * 入金を検索する
     *
     * @param array $conditions 検索条件
     * @param array $options 検索オプション
     */
    public function execute(array $conditions = [], array $options = [])
    {
        // 受注配送先単位
        $query = OrderDestinationModel::query()
        ->with([
            'orderHdr',
            'orderHdr.cust',
        ])
        ->orderBy('t_order_hdr_id', 'asc')
        ->orderBy('order_destination_seq', 'asc')
        ;

        // 条件を設定
        $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

        // 補足情報から追加で検索条件を設定する
        $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

        // ページネーション
        if( ( $options['should_paginate'] ?? false ) === true) {
            if (isset($options['page'])) {
                Paginator::currentPageResolver(function () use ($options) {
                    return $options['page'];
                });
            }
            $options['limit'] = $options['limit'] ?? config('esm.default_page_size.master');
            return $query->paginate($options['limit']);
        }

        return $query->get();
    }

    /**
     * 検索条件を組み上げる
     */
    public function setConditions($query, $conditions): Builder
    {
        // 企業アカウントID
        if( strlen( $conditions['m_account_id'] ?? '' ) == 0 ){
            throw new InvalidArgumentException('企業アカウントIDが指定されていません');
        }
        $query->where('t_order_destination.m_account_id', $conditions['m_account_id']);

        // 受注ID
        if( strlen( $conditions['t_order_hdr_id'] ?? '' ) > 0 ){
            $query->where('t_order_destination.t_order_hdr_id', '=', $conditions['t_order_hdr_id']);
        }

        /**
         * 受注基本にかかる条件
         */
        $query->whereHas('orderHdr', function( $query ) use ( $conditions ) {
            // 企業アカウントID
            $query->where('m_account_id', $conditions['m_account_id']);
            // 論理削除
            if( isset( $conditions['with_deleted'] ) ){
                if( $conditions['with_deleted'] == 1 ) {
                    $query->where('cancel_timestamp', 'NOT LIKE', '0000%');
                }
            }
            else{
                $query->where('cancel_timestamp', 'LIKE', '0000%');
            }
            // 受注日from
            if( strlen( $conditions['order_datetime_from'] ?? '' ) > 0 ){
                $query->where('order_datetime', '>=', $conditions['order_datetime_from']);
            }
            // 受注日to
            if( strlen( $conditions['order_datetime_to'] ?? '' ) > 0 ){
                $query->where('order_datetime', '<=', $conditions['order_datetime_to']);
            }
            // 注文主ID
            if( strlen( $conditions['m_cust_id'] ?? '' ) > 0 ){
                $query->where('m_cust_id', '=', $conditions['m_cust_id']);
            }
            // 請求先ID
            if( strlen( $conditions['m_cust_id_billing'] ?? '' ) > 0 ){
                $query->where('m_cust_id_billing', '=', $conditions['m_cust_id_billing']);
            }
        });

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
        if(isset($options['columns'])){
            $query->select($options['columns']);
        }

        /**
         * @todo リレーションのeagerload指定
         * 適宜条件も含める
         */
        if(isset($options['with'])){
            $query->with($options['with']);
        }

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