<?php
namespace App\Modules\Sample\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Cc\Base\CustModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SearchSample implements SearchSampleInterface
{
    /**
     * デフォルトの検索条件
     */
    protected $defaultConditions = [
        // 'with_deleted' => '0',
        // 'delete_flg' => '0',
    ];

    /**
     * デフォルトの検索オプション
     */
    protected $defaultOptions = [
    ];

    /**
     * デフォルトのソート条件
     * 指定がなければ主テーブルの主キーをデフォルトのソート条件とする
     * 例: ['id' => 'desc']
     */
    protected $defaultSorts = [
    ];


    /**
     * @throws InvalidArgumentException 企業アカウントIDが指定されていない場合
     */
    public function execute(array $conditions, array $options): Collection|LengthAwarePaginator
    {
        ModuleStarted::dispatch(__CLASS__, ['conditions' => $conditions, 'options' => $options]);

        try{
            // 検索処理
            $query = CustModel::query();
            // 副テーブルでソートする場合は、ここでjoinする

            // 検索条件を組み上げる
            $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

            // 補足情報から追加で検索条件を設定する
            $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

            // ペジネーション設定
            if(isset($options['should_paginate']) && $options['should_paginate'] === true) {
                if(isset($options['limit'])) {
                    $result = $query->paginate($options['limit']);
                } else {
                    $result = $query->paginate(config('esm.default_page_size.cc'));
                }
            } else {
                // ペジネーションしない場合は、getで取得し、Eloquentのコレクションを返す
                $result = $query->get();
            }
        }catch(\Exception $e){
            ModuleFailed::dispatch(__CLASS__, ['conditions' => $conditions, 'options' => $options], $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, $result->toArray());
        return $result;

    }

    /**
     * 検索条件を組み上げる
     */
    private function setConditions($query, $conditions): Builder
    {
        // 検索条件を組み上げる
        // 企業アカウントID
        if(isset($conditions['m_account_id'])) {
            $query->where('m_account_id', $conditions['m_account_id']);
        } else {
            // 企業アカウントIDが指定されていない場合は、エラー
            throw new InvalidArgumentException('企業アカウントIDが指定されていません');
        }

        // 使用区分
        if(isset($conditions['delete_flg'])) {
            $query->whereIn('delete_flg', $conditions['delete_flg']);
        }

        // 顧客区分
        // 顧客ID（複数対応）
        if (isset($conditions['m_cust_id']) && strlen($conditions['m_cust_id']) > 0) {
            $query->whereIn('m_cust_id', explode(',', $conditions['m_cust_id']));
        }

        // 顧客コード
        if (isset($conditions['cust_cd']) && strlen($conditions['cust_cd']) > 0) {
            $query->where('cust_cd', 'like', "{$conditions['cust_cd']}%%");
        }
        // 顧客ランク
        if (isset($conditions['m_cust_runk_id']) && strlen($conditions['m_cust_runk_id']) > 0) {
            $query->whereIn('m_cust_runk_id', explode(",", $conditions['m_cust_runk_id']));
        }

        // 法人名・団体名
        if(isset($conditions['corporate_kanji_fuzzy']) && $conditions['corporate_kanji_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['corporate_kanji']) && strlen($conditions['corporate_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['corporate_kanji']);
                $query->where('corporate_kanji', 'like', "%%{$str}%%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['corporate_kanji']) && strlen($conditions['corporate_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['corporate_kanji']);
                $query->where('corporate_kanji', 'like', "{$str}%%");
            }
        }


        // 法人名・団体名（フリガナ）
        if(isset($conditions['corporate_kana_fuzzy']) && $conditions['corporate_kana_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['corporate_kana']) && strlen($conditions['corporate_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['corporate_kana']);
                $query->where('corporate_kana', 'like', "%%{$str}%%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['corporate_kana']) && strlen($conditions['corporate_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['corporate_kana']);
                $query->where('corporate_kana', 'like', "{$str}%%");
            }
        }

        // 電話番号(勤務先）
        if (isset($conditions['corporate_tel']) && strlen($conditions['corporate_tel']) > 0) {
            $query->where('corporate_tel', 'like', "{$conditions['corporate_tel']}%%");
        }

        // 名前漢字
        if(isset($conditions['name_kanji_fuzzy']) && $conditions['name_kanji_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['name_kanji']) && strlen($conditions['name_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['name_kanji']);
                $query->where('gen_search_name_kanji', 'like', "%%{$str}%%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['name_kanji']) && strlen($conditions['name_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['name_kanji']);
                $query->where('gen_search_name_kanji', 'like', "{$str}%%");
            }
        }

        // フリガナ
        if(isset($conditions['name_kana_fuzzy']) && $conditions['name_kana_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['name_kana']) && strlen($conditions['name_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['name_kana']);
                $query->where('gen_search_name_kana', 'like', "%%{$str}%%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['name_kana']) && strlen($conditions['name_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['name_kana']);
                $query->where('gen_search_name_kana', 'like', "{$str}%%");
            }
        }

        // 性別
        if (isset($conditions['sex_type']) && strlen($conditions['sex_type']) > 0) {
            $query->whereIn('sex_type', explode(",", $conditions['sex_type']));
        }

        // メールアドレス
        if (isset($conditions['email']) && strlen($conditions['email']) > 0) {
            $str = mb_strtolower($conditions['email']);
            $query->whereHas('custEmails', function ($query) use ($str) {
                $query->where('email', 'like', "{$str}%");
            });
        }

        // 電話番号
        if (isset($conditions['tel_forward_match']) && ($conditions['tel_forward_match']) == 1) {
            // 前方一致
            if (isset($conditions['tel']) && strlen($conditions['tel']) > 0) {
                $str = str_replace("-", "", $conditions['tel']);
                $query->whereHas('custTels', function ($query) use ($str) {
                    $query->where('tel', 'like', "{$str}%");
                });
            }
        } else {
            // そうでない場合
            if (isset($conditions['tel']) && strlen($conditions['tel']) > 0) {
                $str = str_replace("-", "", $conditions['tel']);
                $query->whereHas('custTels', function ($query) use ($str) {
                    $query->where('tel', $str);
                });
            }
        }

        // FAX
        if (isset($conditions['fax_forward_match']) && ($conditions['fax_forward_match']) == 1) {
            // 前方一致
            if (isset($conditions['fax']) && strlen($conditions['fax']) > 0) {
                $str = str_replace("-", "", $conditions['fax']);
                //$query->where('fax', 'like', "{$str}%%");


                $query->whereRaw('REPLACE(fax,\'-\',\'\') like ?', "{$str}%%");
            }
        } else {
            // そうでない場合
            if (isset($conditions['fax']) && strlen($conditions['fax']) > 0) {
                $str = str_replace("-", "", $conditions['fax']);
                $query->whereRaw('REPLACE(fax,\'-\',\'\')=?', "{$str}");
            }
        }

        // 郵便番号
        if (isset($conditions['postal']) && strlen($conditions['postal']) > 0) {
            $query->where('postal', 'like', "{$conditions['postal']}%%");
        }

        // 都道府県
        if (isset($conditions['address1']) && strlen($conditions['address1']) > 0) {
            $query->where('address1', '=', $conditions['address1']);
        }

        // 住所
        if (isset($conditions['address2_forward_match']) && ($conditions['address2_forward_match']) == 1) {
            // あいまい検索する場合
            if (isset($conditions['address2']) && strlen($conditions['address2']) > 0) {
                $query->whereRaw("CONCAT(IFNULL(m_cust.address2,''), IFNULL(m_cust.address3,''), IFNULL(m_cust.address4,'')) like ?", '%%' .$conditions['address2'] .'%%');
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['address2']) && strlen($conditions['address2']) > 0) {
                $query->whereRaw("CONCAT(IFNULL(m_cust.address2,''), IFNULL(m_cust.address3,''), IFNULL(m_cust.address4,'')) like ?", $conditions['address2'].'%%');
            }
        }

        // 備考の有無（1:有りのみ(null、sp除外)、2:無しのみ(null、sp)）
        if (isset($conditions['note_existence']) && strlen($conditions['note_existence']) == 1) {
            switch ($conditions['note_existence']) {
                case 1:
                    $query->whereRaw('(note is not null and  note <>\'\')');
                    break;
                case 2:
                    $query->whereRaw('(note is null or note =\'\')');
                    break;
            }
        }

        // 備考
        // あいまい検索のみ
        if (isset($conditions['note']) && strlen($conditions['note']) > 0) {
            $query->where('note', 'like', "%%{$conditions['note']}%%");
        }

        // 購入累計金額FROM
        if (isset($conditions['total_order_money_from']) && strlen($conditions['total_order_money_from']) > 0) {
            $query->where('sumsum.total_order_money', '>=', $conditions['total_order_money_from']);
        }

        // 購入累計金額TO
        if (isset($conditions['total_order_money_to']) && strlen($conditions['total_order_money_to']) > 0) {
            $query->where('sumsum.total_order_money', '<=', $conditions['total_order_money_to']);
        }

        // 購入回数FROM
        if (isset($conditions['total_order_count_from']) && strlen($conditions['total_order_count_from']) > 0) {
            $query->where('sumsum.total_order_count', '>=', $conditions['total_order_count_from']);
        }

        // 購入回数TO
        if (isset($conditions['total_order_count_to']) && strlen($conditions['total_order_count_to']) > 0) {
            $query->where('sumsum.total_order_count', '<=', $conditions['total_order_count_to']);
        }

        // 要注意区分
        if (isset($conditions['alert_cust_type']) && strlen($conditions['alert_cust_type']) > 0) {
            $query->whereIn('alert_cust_type', explode(",", $conditions['alert_cust_type']));
        }

        // 要注意コメント
        if (isset($conditions['alert_cust_comment']) && strlen($conditions['alert_cust_comment']) > 0) {
            // あいまい検索のみ
            $query->where('alert_cust_comment', 'like', "%%{$conditions['alert_cust_comment']}%%");
        }

        // 論理削除判定。
        // どのような状態が削除済みとするかは、それぞれの機能ごとに検討する。
        if(isset($conditions['with_deleted'])) {
            if($conditions['with_deleted'] === '1') {
                // 削除されたものだけを取得
                // 削除データのみ
                $query->whereNot('delete_operator_id', '0');
            } else {
                // 削除データを含む
            }
        }else{
            // 削除されたものは含めない
            $query->where('delete_operator_id', '0');
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
                Log::debug('sorts', $options['sorts']);
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
