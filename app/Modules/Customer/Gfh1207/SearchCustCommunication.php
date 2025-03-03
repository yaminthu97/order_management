<?php

namespace App\Modules\Customer\Gfh1207;

use App\Models\Cc\Gfh1207\CustCommunicationModel;
use App\Modules\Common\CommonModule;
use App\Modules\Customer\Base\SearchCustCommunicationInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class SearchCustCommunication extends CommonModule implements SearchCustCommunicationInterface
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
        't_cust_communication_id' => 'desc'
    ];

    public function execute(array $conditions = [], array $options = [])
    {
        try {
            // 検索処理
            $query = CustCommunicationModel::query();


            $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions), $options);
            // 補足情報から追加で検索条件を設定する
            $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

            // ペジネーション設定
            if(isset($options['should_paginate']) && $options['should_paginate'] === true) {
                if(isset($options['limit'])) {
                    return $query->paginate($options['limit'], ['*'], 'page', $options['page']);
                } else {
                    return $query->paginate(config('esm.default_page_size.master'));
                }
            } else {
                return $query->get();
            }
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }


    /**
     * 検索条件を組み上げる
     */
    private function setConditions($query, $conditions, $options = []): Builder
    {
        $m_accountId = null; // set m_accountId null 

        // validate m_account_id exist or not
        if(isset($options['m_account_id'])) {
            $m_accountId = $options['m_account_id']; // set m_account_id from parameter
        } else {
            $m_accountId = $this->getAccountId(); // set m_account_id from session
        }

        $query->where('m_account_id', $m_accountId);

        // 顧客対応履歴ID
        if (isset($conditions['t_cust_communication_id']) && strlen($conditions['t_cust_communication_id']) > 0) {
            $query->whereIn('t_cust_communication_id', explode(',', $conditions['t_cust_communication_id']));
        }

        // 顧客ID
        if (isset($conditions['m_cust_id']) && strlen($conditions['m_cust_id']) > 0) {
            $query->where('m_cust_id', '=', $conditions['m_cust_id']);
        }

        // 受注ID
        if (isset($conditions['t_order_hdr_id']) && strlen($conditions['t_order_hdr_id']) > 0) {
            $query->where('t_order_hdr_id', '=', $conditions['t_order_hdr_id']);
        }

        // 商品コード
        if (isset($conditions['page_cd']) && strlen($conditions['page_cd']) > 0) {
            $query->where('page_cd', '=', $conditions['page_cd']);
        }

        // 連絡方法
        if (isset($conditions['contact_way_type']) && strlen($conditions['contact_way_type']) > 0) {
            $query->where('contact_way_type', '=', $conditions['contact_way_type']);
        }

        // 販売窓口
        if (isset($conditions['sales_channel']) && strlen($conditions['sales_channel']) > 0) {
            $query->where('sales_channel', '=', $conditions['sales_channel']);
        }

        // 問合せ内容種別
        if (isset($conditions['inquiry_type']) && strlen($conditions['inquiry_type']) > 0) {
            $query->where('inquiry_type', '=', $conditions['inquiry_type']);
        }

        // 問合せ内容種別
        if (isset($conditions['inquiry_type']) && strlen($conditions['inquiry_type']) > 0) {
            $query->where('inquiry_type', '=', $conditions['inquiry_type']);
        }

        // 名前漢字
        if (isset($conditions['name_kanji_flag']) && $conditions['name_kanji_flag'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['name_kanji']) && strlen($conditions['name_kanji']) > 0) {
                $query->where('name_kanji', 'like', "%{$conditions['name_kanji']}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['name_kanji']) && strlen($conditions['name_kanji']) > 0) {
                $query->where('name_kanji', 'like', "{$conditions['name_kanji']}%");
            }
        }

        // 名前カナ
        if (isset($conditions['name_kana_flag']) && $conditions['name_kana_flag'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['name_kana']) && strlen($conditions['name_kana']) > 0) {
                $query->where('name_kana', 'like', "%{$conditions['name_kana']}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['name_kana']) && strlen($conditions['name_kana']) > 0) {
                $query->where('name_kana', 'like', "{$conditions['name_kana']}%");
            }
        }

        // 電話番号
        if (isset($conditions['tel_search_flag']) && $conditions['tel_search_flag'] == 1) {
            // 前方一致検索する場合
            if (isset($conditions['tel']) && strlen($conditions['tel']) > 0) {
                $query->where('tel', 'like', "{$conditions['tel']}%");
            }
        } else {
            // そうでない場合は完全一致
            if (isset($conditions['tel']) && strlen($conditions['tel']) > 0) {
                $query->where('tel', '=', $conditions['tel']);
            }
        }


        // メールアドレス
        if (isset($conditions['email']) && strlen($conditions['email']) > 0) {
            // 前方一致検索のみ
            $query->where('email', 'like', "{$conditions['email']}%");
        }

        // 郵便番号
        if (isset($conditions['postal']) && strlen($conditions['postal']) > 0) {
            // 前方一致検索のみ
            $query->where('postal', 'like', "{$conditions['postal']}%");
        }

        // 都道府県
        if (isset($conditions['address1']) && strlen($conditions['address1']) > 0) {
            $query->where('address1', '=', $conditions['address1']);
        }

        // 住所
        // 前方一致検索のみ
        if (isset($conditions['address2']) && strlen($conditions['address2']) > 0) {
            //ConcatのためwhereRawを使いました
            $query->whereRaw("CONCAT(IFNULL(address2, ''), IFNULL(address3, ''), IFNULL(address4, '')) LIKE ?", [addcslashes($conditions['address2'], '%_\\') . '%']);
        }

        // Checking current is batch or not
        if(isset($options['isBatch']) && $options['isBatch'] === true) {
            // 公開フラグ
            if (isset($conditions['open']) && $conditions['open'] == 1) {
                $query->where('open', '=', $conditions['open']); // validate public flag exist or not
            }
        }

        // 連絡先その他
        if (isset($conditions['note']) && strlen($conditions['note']) > 0) {
            $query->where('note', 'like', $conditions['note'] . '%');
        }

        // タイトル
        if (isset($conditions['title']) && strlen($conditions['title']) > 0) {
            // あいまい検索のみ
            $query->where('title', 'like', '%' . $conditions['title'] . '%');
        }

        // ステータス
        if (isset($conditions['status']) && strlen($conditions['status']) > 0) {
            $query->where('status', '=', $conditions['status']);
        }

        // 分類
        if (isset($conditions['category']) && strlen($conditions['category']) > 0) {
            $query->where('category', '=', $conditions['category']);
        }

        // 受信日FROM
        if (isset($conditions['receive_datetime_from']) && strlen($conditions['receive_datetime_from']) > 0) {
            $query->where('receive_datetime', '>=', $conditions['receive_datetime_from']);
        }

        // 受信日TO
        if (isset($conditions['receive_datetime_to']) && strlen($conditions['receive_datetime_to']) > 0) {
            $query->where('receive_datetime', '<=', $conditions['receive_datetime_to']);
        }

        // 受信者
        if (isset($conditions['receive_operator_id']) && strlen($conditions['receive_operator_id']) > 0) {
            $query->where('receive_operator_id', '=', $conditions['receive_operator_id']);
        }

        // 受信内容
        if (isset($conditions['receive_detail']) && strlen($conditions['receive_detail']) > 0) {
            $query->where('receive_detail', 'like', '%' . $conditions['receive_detail'] . '%');
        }

        // 回答日FROM
        if (isset($conditions['answer_datetime_from']) && strlen($conditions['answer_datetime_from']) > 0) {
            $query->where('answer_datetime', '>=', $conditions['answer_datetime_from']);
        }

        // 回答日TO
        if (isset($conditions['answer_datetime_to']) && strlen($conditions['answer_datetime_to']) > 0) {
            $query->where('answer_datetime', '<=', $conditions['answer_datetime_to']);
        }

        // 回答者
        if (isset($conditions['answer_operator_id']) && strlen($conditions['answer_operator_id']) > 0) {
            $query->where('answer_operator_id', '=', $conditions['answer_operator_id']);
        }

        // 回答内容
        if (isset($conditions['answer_detail']) && strlen($conditions['answer_detail']) > 0) {
            $query->where('answer_detail', 'like', '%' . $conditions['answer_detail'] . '%');
        }

        // エスカレーション担当者
        if (isset($conditions['escalation_operator_id']) && strlen($conditions['escalation_operator_id']) > 0) {
            $query->where('escalation_operator_id', '=', $conditions['escalation_operator_id']);
        }

        // 更新日FROM
        if (isset($conditions['update_timestamp_from']) && strlen($conditions['update_timestamp_from']) > 0) {
            $query->where('update_timestamp', '>=', $conditions['update_timestamp_from']);
        }

        // 更新日TO
        if (isset($conditions['update_timestamp_to']) && strlen($conditions['update_timestamp_to']) > 0) {
            $query->where('update_timestamp', '<=', $conditions['update_timestamp_to']);
        }

        // 対応結果
        if (isset($conditions['resolution_status']) && strlen($conditions['resolution_status']) > 0) {
            $query->where('resolution_status', '=', $conditions['resolution_status']);
        }

        return $query;
    }

    /**
     * 補足情報から追加で検索条件を設定する
     */
    private function setOptions($query, $options): Builder
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
