<?php
namespace App\Modules\Order\Base;

use App\Enums\DeleteFlg;
use App\Models\Order\Base\DeliHdrModel;
use App\Models\Order\Base\PaymentModel;
use App\Models\Order\Gfh1207\OrderHdrModel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

use InvalidArgumentException;

class SearchPaymentAccounting implements SearchPaymentAccountingInterface
{
    /**
     * デフォルトの検索条件
     */
    protected $defaultConditions = [
        // 削除済みデータは除外するため、デフォルトは空
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
        // 出荷基本データ（出荷予定日/確定日のMAX）を取得するサブクエリをセット
        $subQuery = DeliHdrModel::select(
            't_order_hdr_id', 
            DB::raw( 'MAX( deli_plan_date ) as deli_plan_date' ),
            DB::raw( 'MAX( deli_decision_date ) as deli_decision_date' ),
        )
        ->whereNull('cancel_operator_id')
        ->groupBy('t_order_hdr_id');

        $query = PaymentModel::query()
        ->with([
            'orderHdr',
            'orderHdr.deliHdr',
            'orderHdr.orderMemo',
        ])
        ->leftJoinSub($subQuery, 't_deli_hdr_last', function ($join) {
            $join->on('t_payment.t_order_hdr_id', '=', 't_deli_hdr_last.t_order_hdr_id');
        })
        ->select(
            't_payment.t_order_hdr_id',
            't_payment.t_payment_id',
            't_payment.payment_subject',
            't_payment.payment_price',
            't_payment.cust_payment_date',
            't_payment.account_payment_date',
            't_payment.payment_entry_date',
            't_deli_hdr_last.deli_plan_date',
            't_deli_hdr_last.deli_decision_date',
        )
        ->orderBy('t_payment.t_order_hdr_id', 'asc')
        ->orderBy('t_payment.t_payment_id', 'asc')
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
        // 論理削除
        if( isset( $conditions['with_deleted'] ) ){
            if( $conditions['with_deleted'] == 1 ) {
                $query->where('t_payment.delete_flg', '=', DeleteFlg::Notuse->value);
            }
        }
        else{
            $query->where('t_payment.delete_flg', '=', DeleteFlg::Use->value);
        }

        // 企業アカウントID
        if( strlen( $conditions['m_account_id'] ?? '' ) == 0 ){
            throw new InvalidArgumentException('企業アカウントIDが指定されていません');
        }
        $query->where('t_payment.m_account_id', $conditions['m_account_id']);

        /**
         * 入金データにかかる条件
         */
        // 入金登録日from
        if( strlen( $conditions['payment_registration_date_from'] ?? '' ) > 0 ){
            $query->where('t_payment.payment_entry_date', '>=', $conditions['payment_registration_date_from']);
        }
        // 入金登録日to
        if( strlen( $conditions['payment_registration_date_to'] ?? '' ) > 0 ){
            $query->where('t_payment.payment_entry_date', '<=', $conditions['payment_registration_date_to']);
        }
        // 顧客入金日from
        if( strlen( $conditions['customer_payment_date_from'] ?? '' ) > 0 ){
            $query->where('t_payment.cust_payment_date', '>=', $conditions['customer_payment_date_from']);
        }
        // 顧客入金日to
        if( strlen( $conditions['customer_payment_date_to'] ?? '' ) > 0 ){
            $query->where('t_payment.cust_payment_date', '<=', $conditions['customer_payment_date_to']);
        }
        // 口座入金日from
        if( strlen( $conditions['account_deposit_date_from'] ?? '' ) > 0 ){
            $query->where('t_payment.account_payment_date', '>=', $conditions['account_deposit_date_from']);
        }
        // 口座入金日to
        if( strlen( $conditions['account_deposit_date_to'] ?? '' ) > 0 ){
            $query->where('t_payment.account_payment_date', '<=', $conditions['account_deposit_date_to']);
        }
        // 入金科目
        if( strlen( $conditions['deposit_account'] ?? '' ) > 0 ){
            $query->where('t_payment.payment_subject', '=', $conditions['deposit_account']);
        }

        /**
         * 出荷基本（最終日）にかかる条件
         */
        // 出荷予定日from
        if( strlen( $conditions['estimated_shipping_date_from'] ?? '' ) > 0 ){
            $query->where('t_deli_hdr_last.deli_plan_date', '>=', $conditions['estimated_shipping_date_from']);
        }
        // 出荷予定日to
        if( strlen( $conditions['scheduled_ship_date_to'] ?? '' ) > 0 ){
            $query->where('t_deli_hdr_last.deli_plan_date', '<=', $conditions['scheduled_ship_date_to']);
        }
        // 出荷確定日from
        if( strlen( $conditions['shipment_confirmation_date_from'] ?? '' ) > 0 ){
            $query->where('t_deli_hdr_last.deli_decision_date', '>=', $conditions['shipment_confirmation_date_from']);
        }
        // 出荷確定日to
        if( strlen( $conditions['shipment_confirmation_date_to'] ?? '' ) > 0 ){
            $query->where('t_deli_hdr_last.deli_decision_date', '<=', $conditions['shipment_confirmation_date_to']);
        }

        /**
         * 受注基本にかかる条件
         */
        $query->whereHas('orderHdr', function( $query ) use ( $conditions ) {
            // 受注基本：進捗区分
            if( strlen( $conditions['progress_classification'] ?? '' ) > 0 ){
                $query->where('progress_type', '=', $conditions['progress_classification']);
            }
            // 受注基本：入金区分
            if( strlen( $conditions['payment_classification'] ?? '' ) > 0 ){
                \Log::error("***** 入金区分：" . $conditions['payment_classification']);
                $query->where('payment_type', '=', $conditions['payment_classification']);
            }
            // 受注基本：支払方法
            if( strlen( $conditions['payment_method'] ?? '' ) > 0 ){
                $query->where('m_payment_types_id', '=', $conditions['payment_method']);
            }
            // 受注基本：ECサイト
            if( strlen( $conditions['ec_site'] ?? '' ) > 0 ){
                $query->where('m_ecs_id', '=', $conditions['ec_site']);
            }
            // 受注基本：受注方法
            if( strlen( $conditions['order_method'] ?? '' ) > 0 ){
                $query->where('order_type', '=', $conditions['order_method']);
            }
            // 受注基本：出荷指示区分
            if( strlen( $conditions['shipping_instruction_category'] ?? '' ) > 0 ){
                $query->where('deli_instruct_type', '=', $conditions['shipping_instruction_category']);
            }
            // 受注基本：出荷確定区分
            if( strlen( $conditions['shipping_confirmation_category'] ?? '' ) > 0 ){
                $query->where('deli_decision_type', '=', $conditions['shipping_confirmation_category']);
            }
            // 受注基本：顧客ID
            if( strlen( $conditions['m_cust_id_billing'] ?? '' ) > 0 ){
                $query->where('m_cust_id_billing', '=', $conditions['m_cust_id_billing']);
            }

            /**
             * 受注メモにかかる条件
             */
            if( strlen( $conditions['internal_memo'] ?? '' ) > 0 ){
                $query->whereHas('orderMemo', function( $query ) use ( $conditions ) {
                    $query->where('operator_comment', 'LIKE', "%{$conditions['internal_memo']}%");
                });
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
