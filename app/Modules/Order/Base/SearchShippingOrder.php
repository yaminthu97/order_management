<?php
namespace App\Modules\Order\Base;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use App\Models\Order\Gfh1207\OrderDestinationModel;
use App\Modules\Order\Base\SearchShippingOrderInterface;

use App\Enums\ProgressTypeEnum;

class SearchShippingOrder implements SearchShippingOrderInterface
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
        't_order_destination_id' => 'desc'
    ];

    public function execute(array $conditions=[], array $options=[])
    {
        $query = OrderDestinationModel::query();
        
        // 受注日時ソート対応のため受注基本を join する
        $query->join('t_order_hdr', 't_order_hdr.t_order_hdr_id', '=', 't_order_destination.t_order_hdr_id');

        // 検品日ソート対応のため出荷基本を leftJoin する
        $query->leftJoin('t_deli_hdr', function($join) {
            $join->on('t_deli_hdr.t_order_destination_id', '=', 't_order_destination.t_order_destination_id')
                 ->where('t_deli_hdr.cancel_operator_id', '=', 0);
        });

        $query->select([
            't_order_destination.*',
            't_order_hdr.order_datetime',
            't_order_hdr.order_name',
            't_order_hdr.progress_type',
            't_deli_hdr.deli_inspection_date',
        ]);
        

        $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

        // 補足情報から追加で検索条件を設定する
        $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

        // ペジネーション設定
        if(isset($options['should_paginate']) && $options['should_paginate'] === true){
            if (isset($options['page'])) {
                Paginator::currentPageResolver(function () use ($options) {
                    return $options['page'];
                });
            }
            if(isset($options['limit'])){
                return $query->paginate($options['limit']);
            }else{
                return $query->paginate(config('esm.default_page_size.master'));
            }
        }else{
            return $query->get();
        }
    }


    /**
     * 検索条件を組み上げる
     */
    public function setConditions($query, $conditions): Builder
    {
        // 検索条件を組み上げる

        // 出荷連携状態
        // 未設定ならば両方セットする
        if (!isset($conditions['cooperation_status'])) {
            $conditions['cooperation_status'] = [0, 1];
        }

        if (isset($conditions['cooperation_status']) && is_array($conditions['cooperation_status'])) {
            if (in_array('0', $conditions['cooperation_status']) && in_array('1', $conditions['cooperation_status'])) {
                // 連携可能と連携済
                // 連携可能 と 連携済 の両方を取得する
                $query->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('t_order_hdr.progress_type', ProgressTypeEnum::PendingShipment->value)
                                  ->orWhere('t_order_hdr.progress_type', ProgressTypeEnum::Shipping->value);
                        })
                        ->where(function ($query) {
                            $query->where('t_order_destination.gp2_type', '0')
                                  ->orWhereNull('t_order_destination.gp2_type');
                        });
                    })
                    ->orWhere('t_order_destination.gp2_type', '>', '0'); // 連携済 (出荷ステータスが1以上)
                });
            } else
            if (in_array('0', $conditions['cooperation_status'])) {
                // 連携可能
                // t_order_hdr の 出荷ステータスが「出荷待」か「出荷中」かつ、出荷ステータス(gp2_type)が「未連携」=0かnullのものを取得する
                $query->where(function($query){
                    $query->where(function($query){
                        $query->where('t_order_hdr.progress_type', ProgressTypeEnum::PendingShipment->value)
                              ->orWhere('t_order_hdr.progress_type', ProgressTypeEnum::Shipping->value);
                    })
                    ->where(function($query){
                        $query->where('t_order_destination.gp2_type', '0')
                              ->orWhereNull('t_order_destination.gp2_type');
                    });
                });
            } elseif (in_array('1', $conditions['cooperation_status'])) {
                // 連携済
                // 出荷ステータスが1以上に設定されている
                $query->where('t_order_destination.gp2_type', '>', '0');
            }
        }
        
        if ((isset($conditions['order_date_from']) && strlen($conditions['order_date_from']) > 0)
            || (isset($conditions['order_date_to']) && strlen($conditions['order_date_to']) > 0)) {
            // 受注日時FROM
            if (isset($conditions['order_date_from']) && strlen($conditions['order_date_from']) > 0) {
                $query->where('t_order_hdr.order_datetime', '>=', $conditions['order_date_from']);
            }

            // 受注日時TO
            if (isset($conditions['order_date_to']) && strlen($conditions['order_date_to']) > 0) {
                $query->where('t_order_hdr.order_datetime', '<=', $conditions['order_date_to']);
            }
        }
        
        if ((isset($conditions['deli_plan_date_from']) && strlen($conditions['deli_plan_date_from']) > 0)
            || (isset($conditions['deli_plan_date_to']) && strlen($conditions['deli_plan_date_to']) > 0)) {
            // 出荷予定日FROM
            if (isset($conditions['deli_plan_date_from']) && strlen($conditions['deli_plan_date_from']) > 0) {
                $query->where('t_order_destination.deli_plan_date', '>=', $conditions['deli_plan_date_from']);
            }

            // 出荷予定日TO
            if (isset($conditions['deli_plan_date_to']) && strlen($conditions['deli_plan_date_to']) > 0) {
                $query->where('t_order_destination.deli_plan_date', '<=', $conditions['deli_plan_date_to']);
            }
        }
        
        if ((isset($conditions['order_id_from']) && strlen($conditions['order_id_from']) > 0)
            || (isset($conditions['order_id_to']) && strlen($conditions['order_id_to']) > 0)) {
            // 受注ID FROM
            if (isset($conditions['order_id_from']) && strlen($conditions['order_id_from']) > 0) {
                $query->where('t_order_destination.t_order_hdr_id', '>=', $conditions['order_id_from']);
            }

            // 受注ID TO
            if (isset($conditions['order_id_to']) && strlen($conditions['order_id_to']) > 0) {
                $query->where('t_order_destination.t_order_hdr_id', '<=', $conditions['order_id_to']);
            }
        }

        // 商品コード
        if (isset($conditions['item_cd']) && strlen($conditions['item_cd']) > 0) {
            // orderDtl の sell_cd に一致するものを取得する
            $query->whereHas('orderDtls', function($query) use ($conditions){
                $query->where('sell_cd', $conditions['item_cd']);
            });
        }

        // 店舗集計グループ
        if (isset($conditions['store_group']) && strlen($conditions['store_group']) > 0) {
            // orderHdr.cust.custRunk.m_itemname_type_code に一致するものを取得する
            $query->whereHas('orderHdr.cust.custRunk', function($query) use ($conditions){
                $query->where('m_itemname_type_code', $conditions['store_group']);
            });
        }

        // 受注方法
        if (isset($conditions['order_type']) && strlen($conditions['order_type']) > 0) {
            $query->where('t_order_hdr.order_type', $conditions['order_type']);
        }

        // 注文主ID
        if (isset($conditions['cust_id']) && strlen($conditions['cust_id']) > 0) {
            $query->where('t_order_hdr.m_cust_id', $conditions['cust_id']);
        }

        // 注文主氏名
        if (isset($conditions['cust_name']) && strlen($conditions['cust_name']) > 0) {
            $query->where('t_order_hdr.order_name', $conditions['cust_name']);
        }

        // 配送先氏名
        if (isset($conditions['deli_name']) && strlen($conditions['deli_name']) > 0) {
            $query->where('t_order_destination.destination_name', $conditions['deli_name']);
        }

        // 出荷ステータス
        if (isset($conditions['gp2_type']) && is_array($conditions['gp2_type'])) {
            $query->whereIn('t_order_destination.gp2_type', $conditions['gp2_type']);
        }

        // 登録者
        if (isset($conditions['entry_operator_id']) && strlen($conditions['entry_operator_id']) > 0) {
            $query->where('t_order_destination.entry_operator_id', $conditions['entry_operator_id']);
        }

        // 送り状番号
        if (isset($conditions['invoice_num']) && strlen($conditions['invoice_num']) > 0) {
            // shippingLabels の shipping_label_number に一致するものを取得する
            $query->whereHas('shippingLabels', function($query) use ($conditions){
                $query->where('shipping_label_number', $conditions['invoice_num']);
            });
        }

        // 論理削除判定。delete_flgが明示的に指定されていない場合は、削除されていないものを取得する
        if(isset($conditions['with_deleted'])){
            if($conditions['with_deleted'] === '1'){
                // delete_flgが明示的に1の場合は、削除済みのみ取得する
                if(isset($conditions['delete_flg']) && $conditions['delete_flg'] === '1'){
                    // t_order_hdr.cancel_operator_id が1以上のものを取得する
                    $query->where('t_order_hdr.cancel_operator_id', '>', '0');
                }else{
                    // そうでなければ、削除済みと削除されていないもの両方を取得する
                }
            }else{
                $query->where(function($query){
                    $query->where('t_order_hdr.cancel_operator_id', '=', '0')
                          ->orWhereNull('t_order_hdr.cancel_operator_id');
                });
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
        if(isset($options['with'])){
            $query->with($options['with']);
        }

        // orderby
        if(isset($options['sorts'])){
            if(is_array($options['sorts'])){
                foreach($options['sorts'] as $column => $direction){
                    $query->orderBy($column, $direction);
                }
            }else{
                $query->orderBy($options['sorts'], 'asc');
            }
        }else{
            foreach($this->defaultSorts as $column => $direction){
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }
}
