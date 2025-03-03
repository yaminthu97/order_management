<?php

namespace App\Modules\Customer\Base;


use Exception;
use InvalidArgumentException;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

use App\Enums\ProgressTypeEnum;
use App\Models\Cc\Base\CustOrderSumModel;
use App\Models\Cc\Gfh1207\CustModel;
use App\Services\EsmSessionManager;

use App\Modules\Customer\Base\SearchCustomerInterface;

class SearchCustomer implements SearchCustomerInterface
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
        'm_cust_id' => 'asc'
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
            // select() does not work on MAX(newest_order_date); That's why selectRaw is used.
            // ANY_VALUE is used due to not using of group by.
            $latestOrdersSubquery = CustOrderSumModel::query()
                ->select('m_cust_id')
                ->selectRaw('MAX(newest_order_date) as newest_order_date')
                ->selectRaw('ANY_VALUE(total_order_count) as total_order_count')
                ->selectRaw('ANY_VALUE(total_order_money) as total_order_money')
                ->groupBy('m_cust_id');

            $query = CustModel::query()
                ->leftJoinSub($latestOrdersSubquery, 'latest_orders', function ($join) {
                    $join->on('m_cust.m_cust_id', '=', 'latest_orders.m_cust_id');
                })
                ->leftJoin('m_itemname_types', 'm_cust.m_cust_runk_id', '=', 'm_itemname_types.m_itemname_types_id')
                ->select('m_cust.*', 'm_itemname_types.m_itemname_type_name', 'latest_orders.newest_order_date', 'latest_orders.total_order_count', 'latest_orders.total_order_money');

            $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

            // 補足情報から追加で検索条件を設定する
            $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

            // ペジネーション設定
            if (isset($options['should_paginate']) && $options['should_paginate'] === true) {
                if (isset($options['limit'])) {
                    return $query->paginate($options['limit'], ['*'], 'page', $options['page'] ?? 1);
                } else {
                    return $query->paginate(config('esm.default_page_size.cc'));
                }

            } else {
                return $query->get();
            }
        } catch (Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return 'connectionError';
        }
    }

    private function setConditions(Builder $query, array $conditions): Builder
    {
        if (!(isset($conditions['m_account_id'])) && $conditions['m_account_id'] > 0) {
            $query->where('m_account_id', '=', $conditions['m_account_id']);
        }

        // 削除は対象外
        if (!(isset($conditions['delete_include']) && $conditions['delete_include'] == 1)) {
            $query->where('delete_operator_id', '=', '0');
        }


        // 使用区分
        if (isset($conditions['delete_flg']) && sizeof($conditions['delete_flg']) > 0) {
            // delete_flg is same other join table's attribute
            $query->whereIn('m_cust.delete_flg', $conditions['delete_flg']);
        }
        // 顧客ID（複数対応）
        if (isset($conditions['m_cust_id']) && strlen($conditions['m_cust_id']) > 0) {
            // m_cust_id is same other table's attribute
            $query->where('m_cust.m_cust_id', $conditions['m_cust_id']);
        }

        // 顧客コード
        if (isset($conditions['cust_cd']) && strlen($conditions['cust_cd']) > 0) {
            $query->where('cust_cd', 'like', "{$conditions['cust_cd']}%");
        }

        // 顧客ランク
        if (isset($conditions['m_cust_runk_id']) && sizeof($conditions['m_cust_runk_id']) > 0) {
            $query->whereIn('m_cust_runk_id', $conditions['m_cust_runk_id']);
        }

        // 法人名・団体名
        if (isset($conditions['corporate_kanji_fuzzy']) && $conditions['corporate_kanji_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['corporate_kanji']) && strlen($conditions['corporate_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['corporate_kanji']);
                $query->where('corporate_kanji', 'like', "%{$str}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['corporate_kanji']) && strlen($conditions['corporate_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['corporate_kanji']);
                $query->where('corporate_kanji', 'like', "{$str}%");
            }
        }

        // 法人名・団体名（フリガナ）
        if (isset($conditions['corporate_kana_fuzzy']) && $conditions['corporate_kana_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['corporate_kana']) && strlen($conditions['corporate_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['corporate_kana']);
                $query->where('corporate_kana', 'like', "%{$str}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['corporate_kana']) && strlen($conditions['corporate_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['corporate_kana']);
                $query->where('corporate_kana', 'like', "{$str}%");
            }
        }

        // 電話番号（勤務先）
        if (isset($conditions['corporate_tel']) && strlen($conditions['corporate_tel']) > 0) {
            $query->where('corporate_tel', 'like', "{$conditions['corporate_tel']}%");
        }

        // 名前漢字
        if (isset($conditions['name_kanji_fuzzy']) && $conditions['name_kanji_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['name_kanji']) && strlen($conditions['name_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['name_kanji']);
                $query->where('gen_search_name_kanji', 'like', "%{$str}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['name_kanji']) && strlen($conditions['name_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['name_kanji']);
                $query->where('gen_search_name_kanji', 'like', "{$str}%");
            }
        }

        // フリガナ
        if (isset($conditions['name_kana_fuzzy']) && $conditions['name_kana_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($conditions['name_kana']) && strlen($conditions['name_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['name_kana']);
                $query->where('gen_search_name_kana', 'like', "%{$str}%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['name_kana']) && strlen($conditions['name_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $conditions['name_kana']);
                $query->where('gen_search_name_kana', 'like', "{$str}%");
            }
        }

        // 性別
        if (isset($conditions['sex_type']) && sizeof($conditions['sex_type']) > 0) {
            $query->whereIn('sex_type', $conditions['sex_type']);
        }

        // メールアドレス
        if (isset($conditions['email']) && strlen($conditions['email']) > 0) {
            $str = mb_strtolower($conditions['email']);
            $query->whereExists(function ($query) use ($str) {
                $query->select('*')
                    ->from('m_cust_email')
                    ->whereColumn('m_cust_email.m_cust_id', 'm_cust.m_cust_id')
                    ->where('email', 'like', $str . '%');
            });
        }

        // 電話番号
        if (isset($conditions['tel_forward_match']) && ($conditions['tel_forward_match']) == 1) {
            // 前方一致
            if (isset($conditions['tel']) && strlen($conditions['tel']) > 0) {
                $str = str_replace("-", "", $conditions['tel']);
                $query->whereExists(function ($query) use ($str) {
                    $query->select('*')
                        ->from('m_cust_tel')
                        ->whereColumn('m_cust_tel.m_cust_id', 'm_cust.m_cust_id')
                        ->where('tel', 'like', $str . '%');
                });
            }
        } else {
            // そうでない場合
            if (isset($conditions['tel']) && strlen($conditions['tel']) > 0) {
                $str = str_replace("-", "", $conditions['tel']);
                $query->whereExists(function ($query) use ($str) {
                    $query->select('*')
                        ->from('m_cust_tel')
                        ->whereColumn('m_cust_tel.m_cust_id', 'm_cust.m_cust_id')
                         ->where('m_cust_tel.tel', '=', $str);
                });
            }
        }

        // FAX
        if (isset($conditions['fax_forward_match']) && $conditions['fax_forward_match'] == 1) {
            // 前方一致
            if (isset($conditions['fax']) && strlen($conditions['fax']) > 0) {
                $str = str_replace("-", "", $conditions['fax']); // Remove dashes from input
                $query->where(function ($query) use ($str) {
                    $query->where('fax', 'like', "{$str}%"); // Match for forward match
                });
            }
        } else {
            // そうでない場合
            if (isset($conditions['fax']) && strlen($conditions['fax']) > 0) {
                $str = str_replace("-", "", $conditions['fax']); // Remove dashes from input
                $query->where(function ($query) use ($str) {
                    $query->where('fax', '=', $str); // Exact match without dashes
                });
            }
        }

        // 郵便番号
        if (isset($conditions['postal']) && strlen($conditions['postal']) > 0) {
            $query->where('postal', 'like', "{$conditions['postal']}%");
        }

        // 都道府県
        if (isset($conditions['address1']) && strlen($conditions['address1']) > 0) {
            $query->where('address1', '=', $conditions['address1']);
        }
        
        // 住所
        if (isset($conditions['address2_forward_match']) && $conditions['address2_forward_match'] == 1) {
            // あいまい検索 (CONCAT による検索のため DB::raw を使用)
            if (isset($conditions['address2']) && strlen($conditions['address2']) > 0) {
                $query->where(
                    DB::raw("CONCAT(IFNULL(address2, ''), IFNULL(address3, ''), IFNULL(address4, ''))"),
                    'like',
                    '%' . $conditions['address2'] . '%'
                );
            }
        } else {
            // 前方一致 (CONCAT による検索のため DB::raw を使用)
            if (isset($conditions['address2']) && strlen($conditions['address2']) > 0) {
                $query->where(
                    DB::raw("CONCAT(IFNULL(address2, ''), IFNULL(address3, ''), IFNULL(address4, ''))"),
                    'like',
                    $conditions['address2'] . '%'
                );
            }
        }

        // 備考の有無（1:有りのみ(null、sp除外)、2:無しのみ(null、sp)）
        if (isset($conditions['note_existence']) && sizeof($conditions['note_existence']) == 1) {
            switch ($conditions['note_existence']) {
                case in_array(1, $conditions['note_existence']):
                    // Case 1: Note is not null and not an empty string
                    $query->where(function ($query) {
                        $query->whereNotNull('note')->orWhere('note', '<>', '');
                    });
                    break;
                case in_array(2, $conditions['note_existence']):
                    // Case 2: Note is either null or an empty string
                    $query->where(function ($query) {
                        $query->whereNull('note')->orWhere('note', '=', '');
                    });
                    break;
            }
        }

        // 備考
        // あいまい検索のみ
        if (isset($conditions['note']) && strlen($conditions['note']) > 0) {
            $query->where('note', 'like', "%{$conditions['note']}%");
        }

        // 購入累計金額FROM
        if (isset($conditions['total_order_money_from']) && strlen($conditions['total_order_money_from']) > 0) {
            $query->where('total_order_money', '>=', $conditions['total_order_money_from']);
        }

        // 購入累計金額TO
        if (isset($conditions['total_order_money_to']) && strlen($conditions['total_order_money_to']) > 0) {
            $query->where('total_order_money', '<=', $conditions['total_order_money_to']);
        }

        // 購入回数FROM
        if (isset($conditions['total_order_count_from']) && strlen($conditions['total_order_count_from']) > 0) {
            $query->where('total_order_count', '>=', $conditions['total_order_count_from']);
        }

        // 購入回数TO
        if (isset($conditions['total_order_count_to']) && strlen($conditions['total_order_count_to']) > 0) {
            $query->where('total_order_count', '<=', $conditions['total_order_count_to']);
        }

        // 要注意区分
        if (isset($conditions['alert_cust_type']) && sizeof($conditions['alert_cust_type']) > 0) {
            $query->whereIn('alert_cust_type', $conditions['alert_cust_type']);
        }

        // 要注意コメント
        if (isset($conditions['alert_cust_comment']) && strlen($conditions['alert_cust_comment']) > 0) {
            // あいまい検索のみ
            $query->where('alert_cust_comment', 'like', "%{$conditions['alert_cust_comment']}%");
        }

        // 顧客ID（複数対応）
        if (isset($conditions['customer_type']) && sizeof($conditions['customer_type']) > 0) {
            $query->whereIn('customer_type', $conditions['customer_type']);
        }

        // 最新受注日
        if (isset($conditions['newest_order_date_from']) && strlen($conditions['newest_order_date_from']) > 0) {
            $str = Carbon::parse($conditions['newest_order_date_from'])->format('Y-m-d');
            $query->where('newest_order_date', '>=', $conditions['newest_order_date_from']);
        }

        // 最新受注日
        if (isset($conditions['newest_order_date_to']) && strlen($conditions['newest_order_date_to']) > 0) {
            $str = Carbon::parse($conditions['newest_order_date_to'])->format('Y-m-d');
            $query->where('newest_order_date', '<=', $conditions['newest_order_date_to']);
        }

        // 受注日時
        $order_datetime_from = (isset($conditions['order_datetime_from']) && strlen($conditions['order_datetime_from']) > 0) ? true : false;
        $order_datetime_to = (isset($conditions['order_datetime_to']) && strlen($conditions['order_datetime_to']) > 0) ? true : false;
        $cancel_checked = (isset($conditions['with_cancel']) && strlen($conditions['with_cancel']) > 0) ? true : false;
        $return_checked = (isset($conditions['with_return']) && strlen($conditions['with_return']) > 0) ? true : false;

        if ($order_datetime_from || $order_datetime_to) {
            if ($order_datetime_from) {
                $strFrom = Carbon::parse($conditions['order_datetime_from'])->format('Y-m-d H:i:s');
            } else {
                $strFrom = null;
            }
            if ($order_datetime_to) {
                $strTo = Carbon::parse($conditions['order_datetime_to'])->format('Y-m-d H:i:s');
            } else {
                $strTo = null;
            }
            $str = [];
            $str = ['order_datetime_from' => $strFrom, 'order_datetime_to' => $strTo, 'with_cancel' => $cancel_checked, 'with_return' => $return_checked];
            // 受注日時 From
            $query->whereExists(function ($query) use ($str) {
                $query->select('*')
                    ->from('t_order_hdr')
                    ->whereColumn('t_order_hdr.m_cust_id', 'm_cust.m_cust_id');

                if (isset($str['order_datetime_from'])) {
                    $query->where('order_datetime', '>=', $str['order_datetime_from']);
                }
                // 受注日時 To
                if (isset($str['order_datetime_to'])) {
                    $query->where('order_datetime', '<=', $str['order_datetime_to']);
                }

                // キャンセル・含める
                // キャンセルを含める
                if (!($str['with_cancel'])) {
                    $query->where('progress_type', '!=', ProgressTypeEnum::Cancelled->value);
                }

                // 返品を含める
                if (!($str['with_return'])) {
                    $query->where('progress_type', '!=', ProgressTypeEnum::Returned->value);
                }
            });
        }

        // DM発送方法 郵便
        if (isset($conditions['dm_send_letter_flg']) && sizeof($conditions['dm_send_letter_flg']) > 0) {
            $query->whereIn('dm_send_letter_flg', $conditions['dm_send_letter_flg']);
        }

        // DM発送方法 メール
        if (isset($conditions['dm_send_mail_flg']) && sizeof($conditions['dm_send_mail_flg']) > 0) {
            $query->whereIn('dm_send_mail_flg', $conditions['dm_send_mail_flg']);
        }

        return $query;
    }

    private function setOptions($query, $options): Builder
    {
        // orderby
        if (isset($options['sorts'])) {
            if (is_array($options['sorts'])) {
                foreach ($options['sorts'] as $column => $direction) {
                    if ($column === 'm_cust_runk_id') {
                        $column = 'm_itemname_type_name';
                    }
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
