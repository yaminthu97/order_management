<?php
namespace App\Modules\Order\Base;

use App\Models\Order\Base\PaymentModel;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Illuminate\Pagination\Paginator;

class SearchPayment implements SearchPaymentInterface
{
    /**
     * デフォルトの検索条件
     */
    protected $defaultConditions = [
        'with_deleted' => '0',
        'delete_flg' => '0',
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
        $query = PaymentModel::query();

        $query->with(['orderHdr', 'paymentSubject']);

        $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

        // 補足情報から追加で検索条件を設定する
        $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

        // 一覧を返却
        if (isset($options['should_paginate']) && $options['should_paginate'] === true) {
            if (isset($options['page'])) {
                Paginator::currentPageResolver(function () use ($options) {
                    return $options['page'];
                });
            }
            $options['limit'] = $options['limit'] ?? config('esm.default_page_size.master');
            $results = $query->paginate($options['limit']);
            $results->getCollection();
            return $results;
        } elseif (isset($options['should_idList']) && $options['should_idList'] === true) {
            // t_order_destination_id の一覧のみを返す
            return $query->get()->pluck('t_order_destination_id')->toArray();
        } else {
            return $query->get();
        }

    }

    /**
     * 検索条件を組み上げる
     */
    public function setConditions($query, $conditions): Builder
    {
        $conditions = $this->convertArraysToStrings($conditions);

        // 検索条件を組み上げる
        // 企業アカウントID
        if(isset($conditions['m_account_id'])) {
            $query->where('m_account_id', $conditions['m_account_id']);
        } else {
            // 企業アカウントIDが指定されていない場合は、エラー
            throw new InvalidArgumentException('企業アカウントIDが指定されていません');
        }

        // 入金登録日from
        if (isset($conditions['payment_entry_date_from']) && strlen($conditions['payment_entry_date_from']) > 0) {
            $query->where('payment_entry_date', '>=', $conditions['payment_entry_date_from']);
        }

        // 入金登録日to
        if (isset($conditions['payment_entry_date_to']) && strlen($conditions['payment_entry_date_to']) > 0) {
            $query->where('payment_entry_date', '<=', $conditions['payment_entry_date_to']);
        }

        // 顧客入金日from
        if (isset($conditions['cust_payment_date_from']) && strlen($conditions['cust_payment_date_from']) > 0) {
            $query->where('cust_payment_date', '>=', $conditions['cust_payment_date_from']);
        }

        // 顧客入金日to
        if (isset($conditions['cust_payment_date_to']) && strlen($conditions['cust_payment_date_to']) > 0) {
            $query->where('cust_payment_date', '<=', $conditions['cust_payment_date_to']);
        }

        // 口座入金日from
        if (isset($conditions['account_payment_date_from']) && strlen($conditions['account_payment_date_from']) > 0) {
            $query->where('account_payment_date', '>=', $conditions['account_payment_date_from']);
        }

        // 口座入金日to
        if (isset($conditions['account_payment_date_to']) && strlen($conditions['account_payment_date_to']) > 0) {
            $query->where('account_payment_date', '<=', $conditions['account_payment_date_to']);
        }

        // 入金科目
        if(isset($conditions['payment_subject'])) {
            $query->where('payment_subject', $conditions['payment_subject']);
        }

        // 支払方法
        if(isset($conditions['m_payment_types_id'])) {
            $query->whereHas('orderHdr', function($query) use ($conditions) {
                $query->whereIN('m_payment_types_id', explode(',', $conditions['m_payment_types_id']));
            });
        }

        // ECサイト
        if(isset($conditions['m_ecs_id'])) {
            $query->whereHas('orderHdr', function($query) use ($conditions) {
                $query->whereIN('m_ecs_id', explode(',', $conditions['m_ecs_id']));
            });
        }

        // 出荷指示区分
        if(isset($conditions['deli_instruct_type'])) {
            $query->whereHas('orderHdr', function($query) use ($conditions) {
                $query->whereIN('deli_instruct_type', explode(',', $conditions['deli_instruct_type']));
            });
        }

        // 出荷確定区分
        if(isset($conditions['deli_decision_type'])) {
            $query->whereHas('orderHdr', function($query) use ($conditions) {
                $query->whereIN('deli_decision_type', explode(',', $conditions['deli_decision_type']));
            });
        }
        
        // 受注ID t_order_hdr_id
        if(isset($conditions['t_order_hdr_id'])) {
            $query->where('t_order_hdr_id', $conditions['t_order_hdr_id']);
        }

        // 顧客ID
        if(isset($conditions['m_cust_id'])) {
            $query->whereHas('orderHdr', function($query) use ($conditions) {
                $query->where('m_cust_id', explode(',', $conditions['m_cust_id']));
            });
        }

        // ECサイト受注ID
        if(isset($conditions['ec_order_num'])) {
            $query->whereHas('orderHdr', function($query) use ($conditions) {
                $query->where('ec_order_num', explode(',', $conditions['ec_order_num']));
            });
        }

        // 注文日from
        if(isset($conditions['order_date_from'])) {
            $query->whereHas('orderHdr', function($query) use ($conditions) {
                $query->where('order_datetime', '>=', $conditions['order_date_from']);
            });
        }

        // 注文日to
        if(isset($conditions['order_date_to'])) {
            $query->whereHas('orderHdr', function($query) use ($conditions) {
                $query->where('order_datetime', '<=', $conditions['order_date_to']);
            });
        }
        
        // 入金区分
        if(isset($conditions['payment_type'])) {
            $query->whereHas('orderHdr', function($query) use ($conditions) {
                $query->whereIN('payment_type', explode(',', $conditions['payment_type']));
            });
        }

        // 注文者氏名・カナ
        if(isset($conditions['order_name'])) {
            $query->whereHas('orderHdr', function($query) use ($conditions) {
                $conditions['order_name'] = preg_replace('/[ 　]+/', '', $conditions['order_name']);
                $query->where(function($query) use ($conditions) {
                    $query->where('gen_search_order_name', 'like', '%'.$conditions['order_name'].'%')
                        ->orWhere('gen_search_order_name_kana', 'like', '%'.$conditions['order_name'].'%');
                });
            });
        }

        // 論理削除判定。delete_flgが明示的に指定されていない場合は、削除されていないものを取得する
        if(isset($conditions['with_deleted'])) {
            if($conditions['with_deleted'] === '1') {
                // delete_flgが明示的に1の場合は、削除済みのみ取得する
                if(isset($conditions['delete_flg']) && $conditions['delete_flg'] === '1') {
                    $query->where('delete_flg', '1');
                } else {
                    // そうでなければ、削除済みと削除されていないもの両方を取得する
                }
            } else {
                $query->where('delete_flg', '0');
            }
        } else {
            $query->where('delete_flg', '0');
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

    // 配列をカンマ区切りの文字列に変換する
    protected function convertArraysToStrings(array &$conditions)
    {
        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                $conditions[$key] = implode(',', $value);
            }
        }
        return $conditions;
    }
}
