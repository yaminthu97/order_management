<?php

namespace App\Modules\Customer\Gfh1207;

use App\Models\Cc\Gfh1207\CustModel;
use App\Modules\Customer\Base\SearchCcCustomerListInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SearchCcCustomerList implements SearchCcCustomerListInterface
{
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
        'newest_order_date' => 'desc'
    ];

    /**
     * 顧客受付取得
     */
    public function execute(array $conditions = [], array $options = [])
    {
        try {
            $query = CustModel::query()
                        ->leftJoin('m_cust_order_sum', 'm_cust.m_cust_id', '=', 'm_cust_order_sum.m_cust_id')
                        ->leftJoin('m_itemname_types', 'm_cust.m_cust_runk_id', '=', 'm_itemname_types.m_itemname_types_id')
                        ->select('m_cust.*', 'm_cust_order_sum.newest_order_date', 'm_itemname_types.m_itemname_type_name');
            $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

            // 補足情報から追加で検索条件を設定する
            $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

            // ペジネーション設定
            if(isset($options['should_paginate']) && $options['should_paginate'] === true) {
                if(isset($options['limit'])) {
                    return $query->paginate($options['limit'], ['*'], 'page', $conditions['hidden_next_page_no'] ?? 1);
                } else {
                    return $query->paginate(config('esm.default_page_size.cc'));
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
    public function setConditions($query, $conditions): Builder
    {
        // 検索条件を組み上げる
        //削除済みの顧客を検索対象外とする
        $query->where(function($query) {
            $query->where('m_cust.delete_operator_id', 0)
                    ->orWhereNull('m_cust.delete_operator_id');
        });
        

        // 企業アカウントID
        if(isset($conditions['m_account_id'])) {
            $query->where('m_cust.m_account_id', $conditions['m_account_id']);
        } else {
            // 企業アカウントIDが指定されていない場合は、エラー
            throw new InvalidArgumentException('企業アカウントIDが指定されていません');
        }

        // 電話番号
        if (isset($conditions['tel_forward']) && ($conditions['tel_forward']) == 1) {
            // 前方一致
            if (isset($conditions['tel']) && strlen($conditions['tel']) > 0) {
                $str = str_replace("-", "", $conditions['tel']);
                $query->whereExists(function ($query) use($str){
                    $query->select('*')
                        ->from('m_cust_tel')
                        ->whereColumn('m_cust_tel.m_cust_id', 'm_cust.m_cust_id')
                        ->where('m_cust_tel.tel', 'like', $str . '%');
                });
            }
        } else {
            // そうでない場合
            if (isset($conditions['tel']) && strlen($conditions['tel']) > 0) {
                $str = str_replace("-", "", $conditions['tel']);
                $query->whereExists(function ($query) use($str) {
                    $query->select('*')
                        ->from('m_cust_tel')
                        ->whereColumn('m_cust_tel.m_cust_id', 'm_cust.m_cust_id')
                        ->where('m_cust_tel.tel', '=', $str );
                });
            }
        }

        // 顧客コード
        if (isset($conditions['cust_cd']) && strlen($conditions['cust_cd']) > 0) {
            $query->where('m_cust.cust_cd', 'like', "{$conditions['cust_cd']}%");
        }

        // 顧客ID
        if (isset($conditions['m_cust_id']) && strlen($conditions['m_cust_id']) > 0) {
            $query->where('m_cust.m_cust_id', '=', $conditions['m_cust_id']);
        }

        // Web会員番号
        if (isset($conditions['reserve10']) && strlen($conditions['reserve10']) > 0) {
            $query->where('m_cust.reserve10', '=', $conditions['reserve10']);
        }

        // 名前漢字
        if(isset($conditions['name_kanji_fuzzy']) && $conditions['name_kanji_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['name_kanji']) && strlen($conditions['name_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['name_kanji']);
                $query->where('m_cust.gen_search_name_kanji', 'LIKE', "%{$str}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['name_kanji']) && strlen($conditions['name_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['name_kanji']);
                $query->where('m_cust.gen_search_name_kanji', 'LIKE', "{$str}%");
            }
        }

        // 郵便番号
        if (isset($conditions['postal']) && strlen($conditions['postal']) > 0) {
            // 前方一致検索のみ
            $query->where('m_cust.postal', 'LIKE', "{$conditions['postal']}%");
        }

        // フリガナ
        if(isset($conditions['name_kana_fuzzy']) && $conditions['name_kana_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['name_kana']) && strlen($conditions['name_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['name_kana']);
                $query->where('m_cust.gen_search_name_kana', 'LIKE', "%{$str}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['name_kana']) && strlen($conditions['name_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['name_kana']);
                $query->where('m_cust.gen_search_name_kana', 'LIKE', "{$str}%");
            }
        }

        // 都道府県
        if (isset($conditions['address1']) && strlen($conditions['address1']) > 0) {
            $query->where('m_cust.address1', '=', $conditions['address1']);
        }

        // メールアドレス
        if ( isset($conditions['email']) && strlen($conditions['email']) > 0 )
        {
            $str = mb_strtolower( $conditions['email'] );
            $query->whereExists(function ($query) use($str){
                $query->select('*')
                    ->from('m_cust_email')
                    ->whereColumn('m_cust_email.m_cust_id', 'm_cust.m_cust_id')
                    ->where('m_cust_email.email', 'like', $str . '%');
            });
        }

        // 住所
        // 前方一致検索のみ
        if (isset($conditions['address2_forward']) && ($conditions['address2_forward']) == 1) {
            // あいまい検索する場合
            if (isset($conditions['address2']) && strlen($conditions['address2']) > 0) {
                // ConcatのためwhereRawを使いました
                $query->whereRaw("CONCAT(IFNULL(address2, ''), IFNULL(address3, ''), IFNULL(address4, '')) LIKE ?", [ '%' . addcslashes($conditions['address2'], '%_\\') . '%']);

            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['address2']) && strlen($conditions['address2']) > 0) {
                // ConcatのためwhereRawを使いました
                $query->whereRaw("CONCAT(IFNULL(address2, ''), IFNULL(address3, ''), IFNULL(address4, '')) LIKE ?", [addcslashes($conditions['address2'], '%_\\') . '%']);
            }
        }

        // 使用区分
        if (isset($conditions['delete_flg']) && is_array($conditions['delete_flg']) && count($conditions['delete_flg']) > 0) {
            $query->whereIn('m_cust.delete_flg', $conditions['delete_flg']);
        }

        // 要注意区分
        if (isset($conditions['alert_cust_type']) && is_array($conditions['alert_cust_type']) && count($conditions['alert_cust_type']) > 0) {
            $query->whereIn('m_cust.alert_cust_type', $conditions['alert_cust_type']);
        }

        // 顧客ランク
        if (isset($conditions['m_cust_runk_id']) && strlen($conditions['m_cust_runk_id']) > 0) {
            $query->where('m_cust.m_cust_runk_id', '=', $conditions['m_cust_runk_id']);
        }

        // FAX
        if (isset($conditions['fax_forward']) && $conditions['fax_forward'] == 1) {
            // 前方一致検索する場合
            if (isset($conditions['fax']) && strlen($conditions['fax']) > 0) {
                $str = str_replace("-", "", $conditions['fax']);
                $query->where('m_cust.fax', 'LIKE', "{$str}%");
            }
        } else {
            // そうでない場合は完全一致
            if (isset($conditions['fax']) && strlen($conditions['fax']) > 0) {
                $str = str_replace("-", "", $conditions['fax']);
                $query->where('m_cust.fax', '=', $str);
            }
        }

        // 法人名・団体名
        if(isset($conditions['corporate_kanji_fuzzy']) && $conditions['corporate_kanji_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['corporate_kanji']) && strlen($conditions['corporate_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['corporate_kanji']);
                $query->where('m_cust.corporate_kanji', 'LIKE', "%{$str}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['corporate_kanji']) && strlen($conditions['corporate_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['corporate_kanji']);
                $query->where('m_cust.corporate_kanji', 'LIKE', "{$str}%");
            }
        }

        // 備考
        if (isset($conditions['note']) && strlen($conditions['note']) > 0) {
            $query->where('m_cust.note', 'LIKE', "%{$conditions['note']}%");
        }

        // 法人名・団体名（フリガナ）
        if(isset($conditions['corporate_kana_fuzzy']) && $conditions['corporate_kana_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['corporate_kana']) && strlen($conditions['corporate_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['corporate_kana']);
                $query->where('m_cust.corporate_kana', 'LIKE', "%{$str}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['corporate_kana']) && strlen($conditions['corporate_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['corporate_kana']);
                $query->where('m_cust.corporate_kana', 'LIKE', "{$str}%");
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
