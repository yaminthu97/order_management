<?php
namespace App\Modules\Order\Gfh1207;

use App\Modules\Order\Base\SearchCreateNoshiInterface;
use App\Models\Order\Base\OrderDtlNoshiModel;
use App\Services\EsmSessionManager;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Illuminate\Pagination\Paginator;

class SearchCreateNoshi implements SearchCreateNoshiInterface
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
        't_order_dtl_noshi.t_order_dtl_noshi_id' => 'asc'
    ];

    public function __construct(EsmSessionManager $esmSessionManager)
    {
        $this->esmSessionManager = $esmSessionManager;
    }
    /**
     * 受注基本ログを検索する
     *
     * @param array $conditions 検索条件
     * @param array $options 検索オプション
     */
    public function execute(array $conditions = [], array $options = [])
    {
        $query = OrderDtlNoshiModel::query()
            ->leftJoin('m_noshi_detail', 't_order_dtl_noshi.noshi_detail_id', '=', 'm_noshi_detail.m_noshi_detail_id')
            ->leftJoin('t_order_hdr', 't_order_dtl_noshi.t_order_hdr_id', '=', 't_order_hdr.t_order_hdr_id')
            ->leftJoin('m_cust', 't_order_hdr.m_cust_id', '=', 'm_cust.m_cust_id')
            ->leftJoin('t_order_destination', 't_order_dtl_noshi.t_order_destination_id', '=', 't_order_destination.t_order_destination_id')
            ->leftJoin('t_order_dtl', 't_order_dtl_noshi.t_order_dtl_id', '=', 't_order_dtl.t_order_dtl_id')
            ->select('t_order_dtl_noshi.*');
        $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

        // 補足情報から追加で検索条件を設定する
        $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

        // ペジネーション設定
        if(isset($options['should_paginate']) && $options['should_paginate'] === true) {
            if (isset($options['page'])) {
                Paginator::currentPageResolver(function () use ($options) {
                    return $options['page'];
                });
            }
            if(isset($options['limit'])) {
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
        // 検索条件を組み上げる
        // 企業アカウントID
        $query->where('t_order_dtl_noshi.m_account_id',  $this->esmSessionManager->getAccountId());

        $query->where(function ($q) {
            $q->whereNull('t_order_dtl_noshi.cancel_timestamp')
              ->orWhere('t_order_dtl_noshi.cancel_timestamp',0);
        });
        $query->where(function ($q) {
            $q->whereNull('t_order_dtl.cancel_timestamp')
              ->orWhere('t_order_dtl.cancel_timestamp',0);
        });
        // 進捗区分(select)
        if(strlen($conditions['progress_type'] ?? '') != 0){
            $query->where('t_order_hdr.progress_type', $conditions['progress_type']);
        }
        // 受注日時
        if(strlen($conditions['order_date_from'] ?? '') != 0){
            $query->where('t_order_hdr.order_datetime','>=',$conditions['order_date_from']);
        }
        // 受注日時
        if(strlen($conditions['order_date_to'] ?? '') != 0){
            $query->where('t_order_hdr.order_datetime','<',date('Y-m-d',strtotime($conditions['order_date_to']." +1 day")));
        }
        // 注文主顧客ID
        if(strlen($conditions['m_cust_id'] ?? '') != 0){
            $query->where('t_order_hdr.m_cust_id', $conditions['m_cust_id']);
        }
        // 注文主氏名（前方一致）
        if(strlen($conditions['cust_name'] ?? '') != 0){
            // スペースを除去
            $cust_name = str_replace('　', '', str_replace(' ', '', $conditions['cust_name']));
            $query->where('t_order_hdr.gen_search_order_name', 'like',addcslashes($cust_name, '%_\\'). '%');
        }
        // 受注方法(select)
        if(strlen($conditions['order_type_name'] ?? '') != 0){
            $query->where('t_order_hdr.order_type', $conditions['order_type_name']);
        }
        // ECサイト(select)
        if(strlen($conditions['ecs_name'] ?? '') != 0){
            $query->where('t_order_hdr.m_ecs_id', $conditions['ecs_name']);
        }
        // ECサイト注文ID
        if(strlen($conditions['ec_order_num'] ?? '') != 0){
            $query->where('t_order_hdr.ec_order_num',$conditions['ec_order_num']);
        }

        // 送付先氏名（前方一致）
        if(strlen($conditions['destination_name'] ?? '') != 0){
            $query->where('t_order_destination.destination_name', 'like',addcslashes($conditions['destination_name'], '%_\\'). '%');
        }
        // 出荷予定日(from)
        if(strlen($conditions['deli_plan_date_from'] ?? '') != 0){
            $query->where('t_order_destination.deli_plan_date','>=',$conditions['deli_plan_date_from']);
        }
        // 出荷予定日(to)
        if(strlen($conditions['deli_plan_date_to'] ?? '') != 0){
            $query->where('t_order_destination.deli_plan_date','<',date('Y-m-d',strtotime($conditions['deli_plan_date_to']." +1 day")));
        }
        // 配送希望日(from)
        if(strlen($conditions['deli_hope_date_from'] ?? '') != 0){
            $query->where('t_order_destination.deli_hope_date','>=',$conditions['deli_hope_date_from']);
        }
        // 配送希望日(to)
        if(strlen($conditions['deli_hope_date_to'] ?? '') != 0){
            $query->where('t_order_destination.deli_hope_date','<',date('Y-m-d',strtotime($conditions['deli_hope_date_to']." +1 day")));
        }
        // 顧客ランク(select)
        if(strlen($conditions['m_cust_runk_name'] ?? '') != 0){
            $query->where('m_cust.m_cust_runk_id', $conditions['m_cust_runk_name']);
        }
        // 熨斗種類(select)
        if(strlen($conditions['noshi_format_name'] ?? '') != 0){
            $query->where('m_noshi_detail.m_noshi_format_id', $conditions['noshi_format_name']);
        }
        // 商品コード(カンマ区切りでIN)
        if(strlen($conditions['item_cd'] ?? '') != 0){
            // スペースを除去
            $item_cd = str_replace('　', '', str_replace(' ', '', $conditions['item_cd']));
            $items = array_filter(explode(",",$item_cd));
            if(count($items) > 0){
                $query->whereIn('t_order_dtl.sell_cd', $items);
            }
        }
        // 受注ID
        if(strlen($conditions['t_order_hdr_id'] ?? '') != 0){
            $query->where('t_order_dtl_noshi.t_order_hdr_id',$conditions['t_order_hdr_id']);
        }
        // 
        // 熨斗タイプ(select)
        if(strlen($conditions['noshi_type'] ?? '') != 0){
            $query->where('t_order_dtl_noshi.noshi_id',$conditions['noshi_type']);
        }
        // 種別(select)
        if(strlen($conditions['attachment_item_group_name'] ?? '') != 0){
            $query->where('t_order_dtl_noshi.attachment_item_group_id',$conditions['attachment_item_group_name']);
        }
        // 熨斗表書き
        if(strlen($conditions['omotegaki'] ?? '') != 0){
            $query->where('t_order_dtl_noshi.omotegaki','like',addcslashes($conditions['omotegaki'], '%_\\').'%');
        }
        // 名入れ
        if(strlen($conditions['naming'] ?? '') != 0){
            $query->where('t_order_dtl_noshi.search_string','like','%'.addcslashes($conditions['naming'], '%_\\').'%');
        }
        // 生成(select)
        if(strlen($conditions['output_counter'] ?? '') != 0){
            if($conditions['output_counter'] === '1'){
                $query->where('t_order_dtl_noshi.output_counter','>=',1);
            } else if($conditions['output_counter'] === '0'){
                $query->where('t_order_dtl_noshi.output_counter',0);
            }
        }
        // 熨斗ファイル名
        if(strlen($conditions['noshi_file_name'] ?? '') != 0){
            $query->where('t_order_dtl_noshi.noshi_file_name','like',addcslashes($conditions['noshi_file_name'], '%_\\').'%');
        }
        // 名入れパターン(select)
        if(strlen($conditions['noshi_naming_pattern_name'] ?? '') != 0){
            $query->where('t_order_dtl_noshi.m_noshi_naming_pattern_id',$conditions['noshi_naming_pattern_name']);
        }
        // 受注明細熨斗ID
        if(strlen($conditions['t_order_dtl_noshi_id'] ?? '') != 0){
            $query->where('t_order_dtl_noshi.t_order_dtl_noshi_id',$conditions['t_order_dtl_noshi_id']);
        }
        \Log::error($query->toSql());
        return $query;
    }

    /**
     * 補足情報から追加で検索条件を設定する
     */
    public function setOptions($query, $options): Builder
    {
        if(isset($options['with'])){
            $query->with($options['with']);
        }
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
