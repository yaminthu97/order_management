<?php

namespace App\Modules\Order\Base;

use App\Models\Order\Base\OrderDestinationModel;
use App\Models\Order\Base\OrderHdrModel;
use App\Modules\Order\Base\SearchInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class Search implements SearchInterface
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
        //'join_table' => ['t_order_destination'],
        //'with' => ['orderTags', 'orderMemo'],
    ];

    /**
     * デフォルトのソート条件
     */
    protected $defaultSorts = [
        //'t_order_destination_id' => 'asc'
    ];

    /**
     * テーブル名称（ソート順に使用）
     */
    protected $table;
    protected $hdrTable;
    protected $destTable;

    /**
     * 進捗区分の色
     */
    protected $progressTypeColorClass = [
        '0' => 'c-states--02',
        '10' => 'c-states--02',
        '20' => 'c-states--02',
        '30' => 'c-states--02',
        '40' => 'c-states--03',
        '50' => 'c-states--04',
        '60' => 'c-states--05',
        '70' => 'c-states--06',
        '80' => 'c-states--07',
        '90' => 'c-states--08',
        '100' => 'c-states--09',
    ];

    public function execute(array $conditions = [], array $options = [])
    {
        // ソートに使用するテーブル名を取得
        $this->hdrTable = (new OrderHdrModel())->getTable();
        $this->destTable = (new OrderDestinationModel())->getTable();

        // 検索処理
        $query = OrderHdrModel::query();

        // 設定されたテーブルを join する
        $query = $this->setJoinTable($query, array_merge($this->defaultOptions, $options));

        // 検索条件を構築する
        $query = $this->setCondition($query, array_merge($this->defaultConditions, $conditions));

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
            $results->getCollection()->transform(function ($item) {
                // paginate 取得後に追加情報を付与
                return  $this->addWarningInfo($item);
            });
            return $results;
        } elseif (isset($options['should_idList']) && $options['should_idList'] === true) {
            // t_order_destination_id の一覧のみを返す
            return $query->get()->pluck('t_order_destination_id')->toArray();
        } else {
            return $query->get();
        }
    }

    /**
     * 数値項目で、複数条件を指定された場合の条件セット
     */
    protected function setWhereOrTypeColumn($query, $columnValue, $columnName, $isNullZero = true, $tableName = '')
    {
        $selectTableName = !empty($tableName) ? $tableName : $this->table;

        $columnValues = explode(',', $columnValue);

        if(count($columnValues) == 1) {
            if($columnValue == 0 && $isNullZero) {
                return $query->where(function ($query) use ($selectTableName, $columnName) {
                    $query->orWhere($selectTableName. '.'. $columnName, '=', 0);
                    $query->orWhereNull($selectTableName. '.'. $columnName);
                });
            } else {
                return $query->where($selectTableName. '.'. $columnName, '=', $columnValue);
            }
        } elseif(count($columnValues) > 1) {
            return $query->where(function ($query) use ($selectTableName, $columnName, $columnValues, $isNullZero) {
                foreach($columnValues as $cValue) {
                    if($cValue == 0 && $isNullZero) {
                        $query->orWhereNull($selectTableName. '.'. $columnName);
                    }
                    $query->orWhere($selectTableName. '.'. $columnName, '=', $cValue);
                }
            });
        }
    }


    protected function setJoinTable(Builder $query, array $options): Builder
    {
        // 既存の検索に対応は難しいため join での結合を行う
        if (!empty($options['join_table'])) {
            foreach ($options['join_table'] as $joinTable) {
                if ($joinTable === 't_order_destination') {
                    $query->join('t_order_destination', 't_order_hdr.t_order_hdr_id', '=', 't_order_destination.t_order_hdr_id');
                }
            }
        }
        return $query;
    }

    protected function setCondition(Builder $query, array $conditions): Builder
    {
        if(!empty($conditions)) {
            $conditions = $this->convertArraysToStrings($conditions);

            $this->table = $query->getModel()->getTable();

            // 進捗区分
            if (isset($conditions['progress_type']) && strlen($conditions['progress_type']) > 0) {
                $query->whereIn('progress_type', explode(',', $conditions['progress_type']));
            }

            // 進捗区分自動手動
            if (isset($conditions['progress_type_auto_self']) && strlen($conditions['progress_type_auto_self']) > 0) {
                $query = $this->setWhereOrTypeColumn($query, $conditions['progress_type_auto_self'], 'progress_type_self_change');
            }

            // 進捗区分変更日時from
            if (isset($conditions['progress_update_datetime_from']) && strlen($conditions['progress_update_datetime_from']) > 0) {
                $query->where('progress_update_datetime', '>=', $conditions['progress_update_datetime_from']);
            }

            // 進捗区分変更日時to
            if (isset($conditions['progress_update_datetime_to']) && strlen($conditions['progress_update_datetime_to']) > 0) {
                $query->where('progress_update_datetime', '<=', $conditions['progress_update_datetime_to']);
            }

            // 要注意顧客
            if (isset($conditions['alert_cust_check_type']) && strlen($conditions['alert_cust_check_type']) > 0) {
                $query->whereIn('alert_cust_check_type', explode(',', $conditions['alert_cust_check_type']));
            }

            //住所エラー
            if (isset($conditions['address_check_type']) && strlen($conditions['address_check_type']) > 0) {
                $query->whereIn('address_check_type', explode(',', $conditions['address_check_type']));
            }

            //配達指定日エラー
            if (isset($conditions['deli_hope_date_check_type']) && strlen($conditions['deli_hope_date_check_type']) > 0) {
                $query->whereIn('deli_hope_date_check_type', explode(',', $conditions['deli_hope_date_check_type']));
            }

            //与信区分
            if (isset($conditions['credit_type']) && strlen($conditions['credit_type']) > 0) {
                $query->whereIn('credit_type', explode(',', $conditions['credit_type']));
            }

            //引当区分
            if (isset($conditions['reservation_type']) && strlen($conditions['reservation_type']) > 0) {
                $query->whereIn('reservation_type', explode(',', $conditions['reservation_type']));
            }

            //入金区分
            if (isset($conditions['payment_type']) && strlen($conditions['payment_type']) > 0) {
                $query->whereIn('payment_type', explode(',', $conditions['payment_type']));
            }

            //出荷指示区分
            if (isset($conditions['deli_instruct_type']) && strlen($conditions['deli_instruct_type']) > 0) {
                $query->whereIn('deli_instruct_type', explode(',', $conditions['deli_instruct_type']));
            }

            //出荷確定区分
            if (isset($conditions['deli_decision_type']) && strlen($conditions['deli_decision_type']) > 0) {
                $query->whereIn('deli_decision_type', explode(',', $conditions['deli_decision_type']));
            }
            //決済ステータス
            if (isset($conditions['settlement_sales_type']) && strlen($conditions['settlement_sales_type']) > 0) {
                $query->whereIn('settlement_sales_type', explode(',', $conditions['settlement_sales_type']));
            }

            //決済売上計上区分
            if (isset($conditions['disp_settlement_sales_type']) && strlen($conditions['disp_settlement_sales_type']) > 0) {
                $query->whereIn('sales_status_type', explode(',', $conditions['disp_settlement_sales_type']));
            }

            //売上ステータス反映区分
            if (isset($conditions['sales_status_type']) && strlen($conditions['sales_status_type']) > 0) {
                $query->whereIn('sales_status_type', explode(',', $conditions['sales_status_type']));
            }

            if (isset($conditions['order_date'])) {
                $this->setWhereConditionOrderDate($query, $conditions['order_date']);
            }

            if ((isset($conditions['order_datetime_from']) && strlen($conditions['order_datetime_from']) > 0)
                || (isset($conditions['order_datetime_to']) && strlen($conditions['order_datetime_to']) > 0)) {
                //受注日時FROM
                if (isset($conditions['order_datetime_from']) && strlen($conditions['order_datetime_from']) > 0) {
                    $query->where('order_datetime', '>=', $conditions['order_datetime_from']);
                }

                //受注日時TO
                if (isset($conditions['order_datetime_to']) && strlen($conditions['order_datetime_to']) > 0) {
                    $query->where('order_datetime', '<=', $conditions['order_datetime_to']);
                }
            } elseif(isset($conditions['display_period']) && strlen($conditions['display_period']) > 0) {

                if($conditions['display_period'] != 9) {
                    //表示期間
                    $now = Carbon::now();

                    $query->where('order_datetime', '<=', $now->format('Y-m-d H:i:s'));

                    $dateFrom = Carbon::now();

                    switch($conditions['display_period']) {
                        case 1:
                            $dateFrom = $now;
                            break;
                        case 2:
                            $dateFrom = $now->subDay();
                            break;
                        case 3:
                            $dateFrom = $now->subDays(2);
                            break;
                        case 4:
                            $dateFrom = $now->startOfWeek(Carbon::MONDAY);
                            break;
                        case 5:
                            $dateFrom = $now->startOfMonth();
                            break;
                        case 6:
                            $dateFrom = $now->subMonths(3);
                            break;
                        case 7:
                            $dateFrom = $now->subMonths(6);
                            break;
                        case 8:
                            $dateFrom = $now->subYear();
                            break;
                    }

                    //表示開始時刻
                    if (isset($conditions['order_time_from']) && strlen($conditions['order_time_from']) > 0) {
                        $dateFrom = $dateFrom->format('Y-m-d'). ' '. $conditions['order_time_from'];
                    } else {
                        $dateFrom = $dateFrom->format('Y-m-d');
                    }


                    $query->where('order_datetime', '>=', $dateFrom);
                }
            }

            // 支払方法
            if (isset($conditions['m_payment_types_id']) && strlen($conditions['m_payment_types_id']) > 0) {
                //$query->whereIn($this->table. '.m_payment_types_id', explode(',', $conditions['m_payment_types_id']));
                $query->whereIn('m_payment_types_id', explode(',', $conditions['m_payment_types_id']));
            }

            //受注ID
            if (isset($conditions['t_order_hdr_id']) && strlen($conditions['t_order_hdr_id']) > 0) {
                $query->whereIn($this->table. '.t_order_hdr_id', explode(',', $conditions['t_order_hdr_id']));
                //$query->whereIn('t_order_hdr_id', explode(',', $conditions['t_order_hdr_id']));
            }

            //即日配送
            if (isset($conditions['immediately_deli_flg']) && strlen($conditions['immediately_deli_flg']) > 0) {
                $query = $this->setWhereOrTypeColumn($query, $conditions['immediately_deli_flg'], 'immediately_deli_flg');
            }

            //楽天スーパーDEAL
            if (isset($conditions['rakuten_super_deal_flg']) && strlen($conditions['rakuten_super_deal_flg']) > 0) {
                $query = $this->setWhereOrTypeColumn($query, $conditions['rakuten_super_deal_flg'], 'rakuten_super_deal_flg');
            }

            //同梱
            if (isset($conditions['bundle']) && strlen($conditions['bundle']) > 0) {
                if($conditions['bundle'] == 1) {
                    $query->where(function ($q) {
                        $q->whereNotNull('bundle_source_ids')
                          ->where('bundle_source_ids', '<>', '');
                    });
                }
            }

            //ECサイト
            if (isset($conditions['m_ecs_id']) && strlen($conditions['m_ecs_id']) > 0) {
                $query->whereIn('m_ecs_id', explode(',', $conditions['m_ecs_id']));
            }

            //ECサイト注文ID
            if (isset($conditions['ec_order_num']) && strlen($conditions['ec_order_num']) > 0) {
                $query->where('ec_order_num', '=', $conditions['ec_order_num']);
            }

            //リピート注文
            if (isset($conditions['repeat_order']) && strlen($conditions['repeat_order']) > 0) {
                $query->whereIn('repeat_flg', explode(',', $conditions['repeat_order']));
            }

            //受注担当者
            if (isset($conditions['order_operator_id']) && strlen($conditions['order_operator_id']) > 0) {
                $query->where('order_operator_id', '=', $conditions['order_operator_id']);
            }

            //最終更新担当者
            if (isset($conditions['update_operator_id']) && strlen($conditions['update_operator_id']) > 0) {
                $query->where($this->table. '.update_operator_id', '=', $conditions['update_operator_id']);
                //$query->where('update_operator_id', '=', $conditions['update_operator_id']);
            }

            //受注方法
            if (isset($conditions['order_type']) && strlen($conditions['order_type']) > 0) {
                $query->whereIN('order_type', explode(',', $conditions['order_type']));
            }

            //ギフト
            if (isset($conditions['gift_flg']) && strlen($conditions['gift_flg']) > 0) {
                $query->whereIn('gift_flg', explode(',', $conditions['gift_flg']));
            }

            //警告注文
            if (isset($conditions['alert_order_flg']) && strlen($conditions['alert_order_flg']) > 0) {
                $query = $this->setWhereOrTypeColumn($query, $conditions['alert_order_flg'], 'alert_order_flg');
            }

            //合計金額FROM
            if (isset($conditions['total_price_from']) && strlen($conditions['total_price_from']) > 0) {
                $query->where('sell_total_price', '>=', $conditions['total_price_from']);
            }

            //合計金額TO
            if (isset($conditions['total_price_to']) && strlen($conditions['total_price_to']) > 0) {
                $query->where('sell_total_price', '<=', $conditions['total_price_to']);
            }

            //請求金額FROM
            if (isset($conditions['order_total_price_from']) && strlen($conditions['order_total_price_from']) > 0) {
                $query->where('order_total_price', '>=', $conditions['order_total_price_from']);
            }

            //請求金額TO
            if (isset($conditions['order_total_price_to']) && strlen($conditions['order_total_price_to']) > 0) {
                $query->where('order_total_price', '<=', $conditions['order_total_price_to']);
            }

            //送料FROM
            if (isset($conditions['shipping_fee_from']) && strlen($conditions['shipping_fee_from']) > 0) {
                $query->where($this->destTable. '.shipping_fee', '>=', $conditions['shipping_fee_from']);
                //$query->where('shipping_fee', '>=', $conditions['shipping_fee_from']);
            }

            //送料TO
            if (isset($conditions['shipping_fee_to']) && strlen($conditions['shipping_fee_to']) > 0) {
                $query->where($this->destTable. '.shipping_fee', '<=', $conditions['shipping_fee_to']);
                //$query->where('shipping_fee', '<=', $conditions['shipping_fee_to']);
            }

            //備考の有無
            if (isset($conditions['order_comment_flg']) && strlen($conditions['order_comment_flg']) > 0) {
                $orderCommentFlg = explode(',', $conditions['order_comment_flg']);

                if(count($orderCommentFlg) == 1) {
                    if($orderCommentFlg[0] == 1) {
                        //$query->whereRaw("IFNULL(order_comment, '') <> '' ");
                        $query->where(function ($q) {
                            $q->where('order_comment', '<>', '')
                              ->orWhereNull('order_comment');
                        });
                    } elseif($orderCommentFlg[0] == 0) {
                        //$query->whereRaw("IFNULL(order_comment, '') = '' ");
                        $query->where(function ($q) {
                            $q->where('order_comment', '=', '')
                              ->orWhereNull('order_comment');
                        });
                    }
                }
            }

            //備考
            if (isset($conditions['order_comment']) && strlen($conditions['order_comment']) > 0) {
                if(isset($conditions['order_comment_search_flg']) && strlen($conditions['order_comment_search_flg']) > 0) {
                    if($conditions['order_comment_search_flg'] == 1) {
                        $query->where('order_comment', 'like', '%'. addcslashes($conditions['order_comment'], '%_\\'). '%');
                    } else {
                        $query->where('order_comment', 'like', addcslashes($conditions['order_comment'], '%_\\'). '%');
                    }
                } else {
                    $query->where('order_comment', 'like', addcslashes($conditions['order_comment'], '%_\\'). '%');
                }
            }

            //社内メモの有無
            if (isset($conditions['operator_comment_flg']) && strlen($conditions['operator_comment_flg']) > 0) {
                $operatorCommentFlg = explode(',', $conditions['operator_comment_flg']);

                if(count($operatorCommentFlg) == 1) {
                    if($operatorCommentFlg[0] == 1) {
                        $query->whereHas('orderMemo', function ($q) {
                            $q->where('operator_comment', '<>', '');
                        })->get();
                    } elseif($operatorCommentFlg[0] == 0) {
                        $query->whereHas('orderMemo', function ($q) {
                            $q->where('operator_comment', '=', '');
                        })->get();
                    }
                }
            }

            //社内メモ
            if (isset($conditions['operator_comment']) && strlen($conditions['operator_comment']) > 0) {
                if(isset($conditions['operator_comment_search_flg']) && strlen($conditions['operator_comment_search_flg']) > 0) {
                    if($conditions['operator_comment_search_flg'] == 1) {
                        $query->whereHas('orderMemo', function ($q) use ($conditions) {
                            $q->where('operator_comment', 'like', '%'. addcslashes($conditions['operator_comment'], '%_\\'). '%');
                        })->get();
                    } else {
                        $query->whereHas('orderMemo', function ($q) use ($conditions) {
                            $q->where('operator_comment', 'like', addcslashes($conditions['operator_comment'], '%_\\'). '%');
                        })->get();
                    }
                } else {
                    $query->whereHas('orderMemo', function ($q) use ($conditions) {
                        $q->where('operator_comment', 'like', addcslashes($conditions['operator_comment'], '%_\\'). '%');
                    })->get();
                }
            }

            //受注キャンセル日時FROM
            if (isset($conditions['cancel_datetime_from']) && strlen($conditions['cancel_datetime_from']) > 0) {
                $query->where('cancel_timestamp', '>=', $conditions['cancel_datetime_from']);
            }

            //受注キャンセル日時TO
            if (isset($conditions['cancel_datetime_to']) && strlen($conditions['cancel_datetime_to']) > 0) {
                $query->where('cancel_timestamp', '<=', $conditions['cancel_datetime_to']);
                $query->where(function ($q) {
                    $q->whereNotNull('cancel_timestamp')
                      ->where('cancel_timestamp', '<>', '0000-00-00 00:00:00.000000');
                });
            }

            //領収証最終出力日時FROM
            if (isset($conditions['receipt_datetime_from']) && strlen($conditions['receipt_datetime_from']) > 0) {
                $query->where('last_receipt_datetime', '>=', $conditions['receipt_datetime_from']);
            }

            //領収証最終出力日時TO
            if (isset($conditions['receipt_datetime_to']) && strlen($conditions['receipt_datetime_to']) > 0) {
                $query->where('last_receipt_datetime', '<=', $conditions['receipt_datetime_to']);
            }

            //受注タグ(含む)
            if (isset($conditions['order_tags_include']) && strlen($conditions['order_tags_include']) > 0) {
                // (含むのみチェックされているものを抽出)
                $includeTags = explode(',', $conditions['order_tags_include']);
                if (isset($conditions['order_tags_exclude']) && strlen($conditions['order_tags_exclude']) > 0) {
                    $includeTags = array_diff(explode(',', $conditions['order_tags_include']), explode(',', $conditions['order_tags_exclude']));
                }
                if (count($includeTags) > 0) {
                    $query->whereHas('orderTags', function ($subQuery) use ($includeTags) {
                        $subQuery->whereIn("m_order_tag_id", $includeTags);
                        $subQuery->where(function ($q) {
                            $q->whereNull("cancel_operator_id")
                              ->orWhere("cancel_operator_id", '=', 0);
                        });
                    })->get();
                    /*
                    $query->whereExists(function ($subQuery) use ($includeTags) {
                        $existsTableName = $this->orderTagTableName;
                        $subQuery->select('*')->from($existsTableName);
                        $subQuery->whereColumn("{$this->table}.t_order_hdr_id", "{$existsTableName}.t_order_hdr_id");

                        $subQuery->whereIn("{$existsTableName}.m_order_tag_id", $includeTags);
                        $subQuery->where(function($q) use ($existsTableName) {
                            $q->whereNull("{$existsTableName}.cancel_operator_id")
                              ->orWhere("{$existsTableName}.cancel_operator_id", '=', 0);
                        });
                    });
                    */
                }
            }

            // 受注タグ(含まない)
            if (isset($conditions['order_tags_exclude']) && strlen($conditions['order_tags_exclude']) > 0) {
                // (含まないのみチェックされているものを抽出)
                $excludeTags = explode(',', $conditions['order_tags_exclude']);
                if (isset($conditions['order_tags_include']) && strlen($conditions['order_tags_include']) > 0) {
                    $excludeTags = array_diff(explode(',', $conditions['order_tags_exclude']), explode(',', $conditions['order_tags_include']));
                }
                if (count($excludeTags) > 0) {
                    $query->whereDoesntHave('orderTags', function ($subQuery) use ($excludeTags) {
                        $subQuery->whereIn("m_order_tag_id", $excludeTags);
                        $subQuery->where(function ($q) {
                            $q->whereNull("cancel_operator_id")
                              ->orWhere("cancel_operator_id", '=', 0);
                        });
                    })->get();
                    /*
                    $query->whereNotExists(function ($subQuery) use ($excludeTags) {
                        $existsTableName = $this->orderTagTableName;
                        $subQuery->select('*')->from($existsTableName);
                        $subQuery->whereColumn("{$this->table}.t_order_hdr_id", "{$existsTableName}.t_order_hdr_id");

                        $subQuery->whereIn("{$existsTableName}.m_order_tag_id", $excludeTags);
                        $subQuery->where(function($q) use ($existsTableName) {
                            $q->whereNull("{$existsTableName}.cancel_operator_id")
                              ->orWhere("{$existsTableName}.cancel_operator_id", '=', 0);
                        });
                    });
                    */
                }
            }

            //電話番号・FAX
            if (isset($conditions['tel_fax']) && strlen($conditions['tel_fax']) > 0) {
                if(isset($conditions['tel_fax_search_flg']) && strlen($conditions['tel_fax_search_flg']) > 0 && $conditions['tel_fax_search_flg'] == 1) {
                    $query->where(function ($subQuery) use ($conditions) {
                        $subQuery->orWhere('order_tel1', 'like', addcslashes($conditions['tel_fax'], '%_\\'). '%');
                        $subQuery->orWhere('order_tel2', 'like', addcslashes($conditions['tel_fax'], '%_\\'). '%');
                        $subQuery->orWhere('order_fax', 'like', addcslashes($conditions['tel_fax'], '%_\\'). '%');
                    });
                } else {
                    $query->where(function ($subQuery) use ($conditions) {
                        $subQuery->orWhere('order_tel1', '=', addcslashes($conditions['tel_fax'], '%_\\'));
                        $subQuery->orWhere('order_tel2', '=', addcslashes($conditions['tel_fax'], '%_\\'));
                        $subQuery->orWhere('order_fax', '=', addcslashes($conditions['tel_fax'], '%_\\'));
                    });
                }
            }

            //注文者氏名・カナ氏名
            if (isset($conditions['order_name']) && strlen($conditions['order_name']) > 0) {
                $orderName = str_replace('　', '', str_replace(' ', '', $conditions['order_name']));
                if(isset($conditions['order_name_search_flg']) && strlen($conditions['order_name_search_flg']) > 0 && $conditions['order_name_search_flg'] == 1) {
                    $query->where(function ($subQuery) use ($conditions, $orderName) {
                        $subQuery->orWhere('gen_search_order_name', 'like', '%'. addcslashes($orderName, '%_\\'). '%');
                        $subQuery->orWhere('gen_search_order_name_kana', 'like', '%'. addcslashes($orderName, '%_\\'). '%');
                    });
                } else {
                    $query->where(function ($subQuery) use ($conditions, $orderName) {
                        $subQuery->orWhere('gen_search_order_name', 'like', addcslashes($orderName, '%_\\'). '%');
                        $subQuery->orWhere('gen_search_order_name_kana', 'like', addcslashes($orderName, '%_\\'). '%');
                    });
                }
            }

            //メールアドレス
            if (isset($conditions['order_email']) && strlen($conditions['order_email']) > 0) {
                if(isset($conditions['order_email_search_flg']) && strlen($conditions['order_email_search_flg']) > 0 && $conditions['order_email_search_flg'] == 1) {
                    $query->where(function ($subQuery) use ($conditions) {
                        $subQuery->orWhere('order_email1', 'like', addcslashes($conditions['order_email'], '%_\\'). '%');
                        $subQuery->orWhere('order_email2', 'like', addcslashes($conditions['order_email'], '%_\\'). '%');
                    });
                } else {
                    $query->where(function ($subQuery) use ($conditions) {
                        $subQuery->orWhere('order_email1', '=', addcslashes($conditions['order_email'], '%_\\'));
                        $subQuery->orWhere('order_email2', '=', addcslashes($conditions['order_email'], '%_\\'));
                    });
                }
            }

            //顧客ID
            if (isset($conditions['m_cust_id']) && strlen($conditions['m_cust_id']) > 0) {
                $query->where('m_cust_id', '=', $conditions['m_cust_id']);
            }

            // 顧客マスタ条件
            if((isset($conditions['m_cust_runk_id']) && strlen($conditions['m_cust_runk_id']) > 0)
                || (isset($conditions['cust_cd']) && strlen($conditions['cust_cd']) > 0)
                || (isset($conditions['alert_cust_type']) && strlen($conditions['alert_cust_type']) > 0)
                || (isset($conditions['cust_reserve10']) && strlen($conditions['cust_reserve10']) > 0)
            ) {

                $query->whereHas('cust', function ($subQuery) use ($conditions) {

                    //顧客ランク
                    if (isset($conditions['m_cust_runk_id']) && strlen($conditions['m_cust_runk_id']) > 0) {
                        $subQuery->whereIn('m_cust_runk_id', explode(',', $conditions['m_cust_runk_id']));
                    }

                    //顧客コード
                    if (isset($conditions['cust_cd']) && strlen($conditions['cust_cd']) > 0) {
                        $subQuery->where('cust_cd', '=', $conditions['cust_cd']);
                    }

                    // 要注意顧客区分
                    if (isset($conditions['alert_cust_type']) && strlen($conditions['alert_cust_type']) > 0) {
                        $subQuery->whereIn('alert_cust_type', explode(',', $conditions['alert_cust_type']));
                    }

                    // Web顧客番号
                    if (isset($conditions['cust_reserve10']) && strlen($conditions['cust_reserve10']) > 0) {
                        $subQuery->whereIn('reserve10', explode(',', $conditions['cust_reserve10']));
                    }

                })->get();
            }

            // 販売情報による商品条件
            if((isset($conditions['sell_cd']) && strlen($conditions['sell_cd']) > 0)
                || (isset($conditions['sell_name']) && strlen($conditions['sell_name']) > 0)
                || (isset($conditions['sell_option']) && strlen($conditions['sell_option']) > 0)
                || (isset($conditions['noshi_flg']) && strlen($conditions['noshi_flg']) > 0)
            ) {
                $query->whereHas('orderDtl', function ($subQuery) use ($conditions) {

                    //販売コード
                    if (isset($conditions['sell_cd']) && strlen($conditions['sell_cd']) > 0) {
                        $subQuery->where('sell_cd', '=', $conditions['sell_cd']);
                    }

                    //販売名
                    if (isset($conditions['sell_name']) && strlen($conditions['sell_name']) > 0) {
                        if(isset($conditions['sell_name_search_flag']) && strlen($conditions['sell_name_search_flag']) > 0 && $conditions['sell_name_search_flag'] == 1) {
                            $subQuery->where('sell_name', 'like', '%'. addcslashes($conditions['sell_name'], '%_\\'). '%');
                        } else {
                            $subQuery->where('sell_name', 'like', addcslashes($conditions['sell_name'], '%_\\'). '%');
                        }
                    }

                    //項目選択肢
                    if (isset($conditions['sell_option']) && strlen($conditions['sell_option']) > 0) {
                        $subQuery->where('sell_option', 'like', '%'. addcslashes($conditions['sell_option'], '%_\\'). '%');
                    }

                    // 熨斗の有無
                    // テーブル名 $existsTableName の子テーブル t_order_dtl_noshi に対しての条件
                    if (isset($conditions['noshi_flg']) && strlen($conditions['noshi_flg']) > 0) {
                        // noshi_flg が 1 の場合、子テーブル t_order_dtl_noshi にレコードが存在するもののみ抽出
                        if ($conditions['noshi_flg'] == 1) {
                            $subQuery->whereHas('orderDtlNoshi', function ($nestedQuery) {
                                // 存在確認
                            });
                        }
                        // noshi_flg が 0 の場合、子テーブル t_order_dtl_noshi にレコードが存在しないもののみ抽出
                        if ($conditions['noshi_flg'] == 0) {
                            $subQuery->whereDoesntHave('orderDtlNoshi', function ($nestedQuery) {
                                // 存在確認
                            });
                        }
                    }
                })->get();
            }

            // 販売情報SKUによる商品条件
            if((isset($conditions['item_cd']) && strlen($conditions['item_cd']) > 0)
                || (isset($conditions['m_warehouse_id']) && strlen($conditions['m_warehouse_id']) > 0)
                || (isset($conditions['temperature_zone']) && strlen($conditions['temperature_zone']) > 0)
                || (isset($conditions['m_suppliers_id']) && strlen($conditions['m_suppliers_id']) > 0)
                || (isset($conditions['direct_deli_flg']) && strlen($conditions['direct_deli_flg']) > 0)
            ) {
                $query->whereHas('orderDtlSku', function ($subQuery) use ($conditions) {

                    //商品コード
                    if (isset($conditions['item_cd']) && strlen($conditions['item_cd']) > 0) {
                        $subQuery->where('item_cd', '=', $conditions['item_cd']);
                    }
                    //配送倉庫
                    if (isset($conditions['m_warehouse_id']) && strlen($conditions['m_warehouse_id']) > 0) {
                        $subQuery->whereIn('m_warehouse_id', explode(',', $conditions['m_warehouse_id']));
                    }

                    //温度帯
                    if (isset($conditions['temperature_zone']) && strlen($conditions['temperature_zone']) > 0) {
                        $subQuery->whereIn('temperature_type', explode(',', $conditions['temperature_zone']));
                    }

                    //仕入先コード
                    if (isset($conditions['m_suppliers_id']) && strlen($conditions['m_suppliers_id']) > 0) {
                        $subQuery->where('m_supplier_id', '=', $conditions['m_suppliers_id']);
                    }

                    //直送
                    if (isset($conditions['direct_deli_flg']) && strlen($conditions['direct_deli_flg']) > 0) {
                        $subQuery->whereIn('direct_delivery_type', explode(',', $conditions['direct_deli_flg']));
                    }
                })->get();
            }


            //後払い.com注文ID
            if (isset($conditions['payment_transaction_id']) && strlen($conditions['payment_transaction_id']) > 0) {
                $query->where('payment_transaction_id', '=', $conditions['payment_transaction_id']);
            }

            //後払い.com決済ステータス
            if (isset($conditions['cb_credit_status']) && strlen($conditions['cb_credit_status']) > 0) {
                $query->whereIn('cb_credit_status', explode(',', $conditions['cb_credit_status']));
            }

            //後払い.com出荷ステータス
            if (isset($conditions['cb_deli_status']) && strlen($conditions['cb_deli_status']) > 0) {
                $query->whereIn('cb_deli_status', explode(',', $conditions['cb_deli_status']));
            }

            //後払い.com請求書送付ステータス
            if (isset($conditions['cb_billed_status']) && strlen($conditions['cb_billed_status']) > 0) {
                $query->whereIn('cb_billed_status', explode(',', $conditions['cb_billed_status']));
            }

            //請求書送付種別
            if (isset($conditions['cb_billed_type']) && strlen($conditions['cb_billed_type']) > 0) {
                $query->whereIn('cb_billed_type', explode(',', $conditions['cb_billed_type']));
            }

            //決済金額差異
            if (isset($conditions['payment_diff_flg']) && strlen($conditions['payment_diff_flg']) > 0) {
                if($conditions['payment_diff_flg'] == 1) {
                    $query->where(function ($q) {
                        $q->whereNull('payment_price')
                          ->orWhere('payment_price', '=', 0);
                    })->whereColumn('payment_price', '<>', 'order_total_price');
                }
            }

            //入金日FROM
            if (isset($conditions['payment_date_from']) && strlen($conditions['payment_date_from']) > 0) {
                $query->where('payment_date', '>=', $conditions['payment_date_from']);
            }

            //入金日TO
            if (isset($conditions['payment_date_to']) && strlen($conditions['payment_date_to']) > 0) {
                $query->where('payment_date', '<=', $conditions['payment_date_to']);
                $query->where(function ($q) {
                    $q->whereNotNull('payment_date')
                      ->where('payment_date', '<>', '0000-00-00');
                });
            }

            //入金金額FROM
            if (isset($conditions['payment_price_from']) && strlen($conditions['payment_price_from']) > 0) {
                $query->where('payment_price', '>=', $conditions['payment_price_from']);
            }

            //入金金額TO
            if (isset($conditions['payment_price_to']) && strlen($conditions['payment_price_to']) > 0) {
                $query->where('payment_price', '<=', $conditions['payment_price_to']);
            }

            //複数配送先
            if (isset($conditions['multiple_deli_flg']) && strlen($conditions['multiple_deli_flg']) > 0) {
                $query->where('multiple_deli_flg', '=', $conditions['multiple_deli_flg']);
            }

            // お届け先関係
            if(
                (isset($conditions['deli_plan_date_from']) && strlen($conditions['deli_plan_date_from']) > 0)
                || (isset($conditions['deli_plan_date_to']) && strlen($conditions['deli_plan_date_to']) > 0)
                || (isset($conditions['deli_hope_date_from']) && strlen($conditions['deli_hope_date_from']) > 0)
                || (isset($conditions['deli_hope_date_to']) && strlen($conditions['deli_hope_date_to']) > 0)
                || (isset($conditions['order_deli_address_check_flag']) && strlen($conditions['order_deli_address_check_flag']) > 0 && $conditions['order_deli_address_check_flag'] == 1)
                || (isset($conditions['destination_address1']) && strlen($conditions['destination_address1']) > 0)
                || (isset($conditions['destination_postal']) && strlen($conditions['destination_postal']) > 0)
                || (isset($conditions['deli_hope_time_cd']) && strlen($conditions['deli_hope_time_cd']) > 0)
                || (isset($conditions['destination_address234']) && strlen($conditions['destination_address234']) > 0)
                || (isset($conditions['destination_name']) && strlen($conditions['destination_name']) > 0)
                || (isset($conditions['t_order_destinaton_id']) && strlen($conditions['t_order_destinaton_id']) > 0)
                || (isset($conditions['invoice_comment_flg']) && strlen($conditions['invoice_comment_flg']) > 0 && strpos($conditions['invoice_comment_flg'], '1') !== false)
                || (isset($conditions['invoice_comment']) && strlen($conditions['invoice_comment']) > 0)
                || (isset($conditions['multi_warehouse_flg']) && strlen($conditions['multi_warehouse_flg']) > 0)
                || (isset($conditions['m_deli_type_id']) && strlen($conditions['m_deli_type_id']) > 0)
                || (isset($conditions['destination_picking_comment_flg']) && strlen($conditions['destination_picking_comment_flg']) > 0)
                || (isset($conditions['destination_picking_comment']) && strlen($conditions['destination_picking_comment']) > 0)
            ) {
                $query->whereHas('orderDestination', function ($subQuery) use ($conditions) {

                    //出荷予定日FROM
                    if (isset($conditions['deli_plan_date_from']) && strlen($conditions['deli_plan_date_from']) > 0) {
                        $subQuery->where('deli_plan_date', '>=', $conditions['deli_plan_date_from']);
                    }

                    //出荷予定日TO
                    if (isset($conditions['deli_plan_date_to']) && strlen($conditions['deli_plan_date_to']) > 0) {
                        $subQuery->where('deli_plan_date', '<=', $conditions['deli_plan_date_to']);
                        $subQuery->where('deli_plan_date', '<>', '0000-00-00');
                    }

                    //配送希望日FROM
                    if (isset($conditions['deli_hope_date_from']) && strlen($conditions['deli_hope_date_from']) > 0) {
                        $subQuery->where('deli_hope_date', '>=', $conditions['deli_hope_date_from']);
                    }

                    //配送希望日TO
                    if (isset($conditions['deli_hope_date_to']) && strlen($conditions['deli_hope_date_to']) > 0) {
                        $subQuery->where('deli_hope_date', '<=', $conditions['deli_hope_date_to']);
                        $subQuery->where('deli_hope_date', '<>', '0000-00-00');
                    }

                    //配送希望時間帯
                    if (isset($conditions['deli_hope_time_cd']) && strlen($conditions['deli_hope_time_cd']) > 0) {
                        $subQuery->whereIn('deli_hope_time_cd', explode(',', $conditions['deli_hope_time_cd']));
                    }

                    //配送先氏名・カナ氏名
                    if (isset($conditions['destination_name']) && strlen($conditions['destination_name']) > 0) {
                        $destinationName = '';
                        if(isset($conditions['destination_search_flag']) && strlen($conditions['destination_search_flag']) && $conditions['destination_search_flag'] == 1) {
                            $destinationName = '%'. addcslashes($conditions['destination_name'], '%_\\') .'%';
                        } else {
                            $destinationName = addcslashes($conditions['destination_name'], '%_\\') .'%';
                        }

                        if(!empty(str_replace('%', '', $destinationName))) {
                            $subQuery->where(function ($subQuery2) use ($destinationName) {
                                $subQuery2->orWhere('destination_name', 'like', $destinationName);
                                $subQuery2->orWhere('destination_name_kana', 'like', $destinationName);
                            });
                        }
                    }

                    //注文・送付先不一致
                    if (isset($conditions['order_deli_address_check_flag']) && strlen($conditions['order_deli_address_check_flag']) > 0 && $conditions['order_deli_address_check_flag'] == 1) {
                        $subQuery->where(
                            DB::raw("CONCAT(IFNULL(order_address1, ''), IFNULL(order_address2, ''), IFNULL(order_address3, ''), IFNULL(order_address4, ''))"),
                            '<>',
                            DB::raw("CONCAT(IFNULL(destination_address1, ''), IFNULL(destination_address2, ''), IFNULL(destination_address3, ''), IFNULL(destination_address4, ''))")
                        );
                    }

                    //送付先都道府県
                    if (isset($conditions['destination_address1']) && strlen($conditions['destination_address1']) > 0) {
                        $subQuery->whereIn('destination_address1', explode(',', $conditions['destination_address1']));
                    }

                    // 配送先郵便番号
                    if (isset($conditions['destination_postal']) && strlen($conditions['destination_postal']) > 0) {
                        $subQuery->where('destination_postal', '=', $conditions['destination_postal']);
                    }

                    // お届け先ID
                    if (isset($conditions['t_order_destinaton_id']) && strlen($conditions['t_order_destinaton_id']) > 0) {
                        $subQuery->whereIn('t_order_destinaton_id', explode(',', $conditions['delivery_postal']));
                    }

                    //送り状コメントの有無(あり)
                    if (isset($conditions['invoice_comment_flg']) && strlen($conditions['invoice_comment_flg']) > 0) {
                        $invoiceComments = explode(',', $conditions['invoice_comment_flg']);
                        if(count($invoiceComments) == 1) {
                            if($invoiceComments[0] == 1) {
                                $subQuery->where(function ($q) {
                                    $q->where("invoice_comment", '<>', '')
                                      ->whereNotNull("invoice_comment");
                                });

                            }
                        }
                    }

                    //送り状コメント
                    if (isset($conditions['invoice_comment']) && strlen($conditions['invoice_comment']) > 0) {
                        $subQuery->where('invoice_comment', 'like', '%'. addcslashes($conditions['invoice_comment'], '%_\\'). '%');
                    }

                    // 複数倉庫引当フラグ
                    if(isset($conditions['multi_warehouse_flg']) && strlen($conditions['multi_warehouse_flg']) > 0) {

                        $subQuery->where(function ($subQuery2) use ($conditions) {
                            // 検索値0はis nullに当てる
                            $multi_warehouse_flg = explode(',', $conditions['multi_warehouse_flg']);
                            $flgNull = \array_search('0', $multi_warehouse_flg);
                            if($flgNull !== false) {
                                $subQuery2->orWhereNull("multi_warehouse_flg");
                                $subQuery2->orWhereIn('multi_warehouse_flg', $multi_warehouse_flg);
                            } else {
                                $subQuery2->whereIn('multi_warehouse_flg', $multi_warehouse_flg);
                            }
                        });
                    }

                    //配送方法
                    if (isset($conditions['m_deli_type_id']) && strlen($conditions['m_deli_type_id']) > 0) {
                        $subQuery->whereIn('m_delivery_type_id', explode(',', $conditions['m_deli_type_id']));
                    }

                    //ピッキングコメントの有無(あり)
                    //(受注配送先 ピッキングコメント)
                    if (isset($conditions['destination_picking_comment_flg']) && strlen($conditions['destination_picking_comment_flg']) > 0) {
                        $pickingComments = explode(',', $conditions['destination_picking_comment_flg']);
                        if(count($pickingComments) == 1) {
                            if($pickingComments[0] == 1) {
                                $subQuery->where('picking_comment', '<>', '')->whereNotNull('picking_comment');
                            }
                        }
                    }

                    //ピッキングコメント
                    //(受注配送先 ピッキングコメント)
                    if (isset($conditions['destination_picking_comment']) && strlen($conditions['destination_picking_comment']) > 0) {
                        $subQuery->where('picking_comment', 'like', '%'. addcslashes($conditions['destination_picking_comment'], '%_\\'). '%');
                    }
                })->get();
            }

            // 配送先予定日関連
            if ((isset($conditions['deli_plan_date_nothing_flg']) && strlen($conditions['deli_plan_date_nothing_flg']) > 0 && $conditions['deli_plan_date_nothing_flg'] == 1)
                || (isset($conditions['deli_hope_date_nothing_flg']) && strlen($conditions['deli_hope_date_nothing_flg']) > 0 && $conditions['deli_hope_date_nothing_flg'] == 1)
            ) {
                $query->whereHas('orderDestination', function ($subQuery) use ($conditions) {
                    //出荷予定日なし
                    if(isset($conditions['deli_plan_date_nothing_flg']) && strlen($conditions['deli_plan_date_nothing_flg']) > 0 && $conditions['deli_plan_date_nothing_flg'] == 1) {
                        $subQuery->whereNotNull('deli_plan_date');
                        $subQuery->where('deli_plan_date', '<>', '0000-00-00');
                    }

                    //配送希望日なし
                    if (isset($conditions['deli_hope_date_nothing_flg']) && strlen($conditions['deli_hope_date_nothing_flg']) > 0 && $conditions['deli_hope_date_nothing_flg'] == 1) {
                        $subQuery->whereNotNull('deli_hope_date');
                        $subQuery->where('deli_hope_date', '<>', '0000-00-00');
                    }
                })->get();
            }

            //送り状コメントの有無(なし)
            if (isset($conditions['invoice_comment_flg']) && strlen($conditions['invoice_comment_flg']) > 0 && strpos($conditions['invoice_comment_flg'], '0') !== false) {
                $invoiceComments = explode(',', $conditions['invoice_comment_flg']);
                if(count($invoiceComments) == 1) {
                    if($invoiceComments[0] == 0) {
                        $query->whereHas('orderDestination', function ($subQuery) use ($conditions) {
                            $subQuery->where('invoice_comment', '<>', '');
                            $subQuery->whereNotNull('invoice_comment');
                        })->get();
                    }
                }
            }


            // 配送関係
            if(
                (isset($conditions['t_deli_hdr_id']) && strlen($conditions['t_deli_hdr_id']) > 0)
                // || (isset($conditions['m_deli_type_id']) && strlen($conditions['m_deli_type_id']) > 0)
                || (isset($conditions['invoice_num']) && strlen($conditions['invoice_num']) > 0)
                || (isset($conditions['deli_decision_date_flg']) && strlen($conditions['deli_decision_date_flg']) > 0 && strpos($conditions['deli_decision_date_flg'], '1') !== false)
                || (isset($conditions['deli_decision_date_from']) && strlen($conditions['deli_decision_date_from']) > 0)
                || (isset($conditions['deli_decision_date_to']) && strlen($conditions['deli_decision_date_to']) > 0)
                || (isset($conditions['picking_comment_flg']) && strlen($conditions['picking_comment_flg']) > 0  && strpos($conditions['picking_comment_flg'], '1') !== false)
                || (isset($conditions['picking_comment']) && strlen($conditions['picking_comment']) > 0)
                // || (isset($conditions['m_warehouse_id']) && strlen($conditions['m_warehouse_id']) > 0)
                // || (isset($conditions['temperature_zone']) && strlen($conditions['temperature_zone']) > 0)
            ) {
                $query->whereHas('deliHdr', function ($subQuery) use ($conditions) {

                    //配送ID
                    if (isset($conditions['t_deli_hdr_id']) && strlen($conditions['t_deli_hdr_id']) > 0) {
                        $subQuery->whereIn('t_deli_hdr_id', explode(',', $conditions['t_deli_hdr_id']));
                    }

                    // //配送方法
                    // if (isset($conditions['m_deli_type_id']) && strlen($conditions['m_deli_type_id']) > 0)
                    // {
                    //     $subQuery->where($existsTableName. '.m_deli_type_id', '=', $conditions['m_deli_type_id']);
                    // }

                    //送り状番号
                    if (isset($conditions['invoice_num']) && strlen($conditions['invoice_num']) > 0) {
                        $subQuery->where(function ($subQuery2) use ($conditions) {
                            $subQuery2->orWhere('invoice_num1', '=', $conditions['invoice_num']);
                            $subQuery2->orWhere('invoice_num2', '=', $conditions['invoice_num']);
                            $subQuery2->orWhere('invoice_num3', '=', $conditions['invoice_num']);
                            $subQuery2->orWhere('invoice_num4', '=', $conditions['invoice_num']);
                            $subQuery2->orWhere('invoice_num5', '=', $conditions['invoice_num']);
                        });
                    }

                    //配送日の有無(あり)
                    if (isset($conditions['deli_decision_date_flg']) && strlen($conditions['deli_decision_date_flg']) > 0) {
                        $deliDecisionDates = explode(',', $conditions['deli_decision_date_flg']);
                        if(count($deliDecisionDates) == 1) {
                            if($deliDecisionDates[0] == 1) {
                                $subQuery->where('deli_decision_date', '<>', '0000-00-00');
                                $subQuery->whereNotNull('deli_decision_date');
                            }
                        }
                    }

                    //配送日FROM
                    if (isset($conditions['deli_decision_date_from']) && strlen($conditions['deli_decision_date_from']) > 0) {
                        $subQuery->where('deli_decision_date', '>=', $conditions['deli_decision_date_from']);
                    }

                    //配送日TO
                    if (isset($conditions['deli_decision_date_to']) && strlen($conditions['deli_decision_date_to']) > 0) {
                        $subQuery->where('deli_decision_date', '<=', $conditions['deli_decision_date_to']);
                    }

                    //ピッキングリストコメントの有無(あり)
                    //(出荷基本 ピッキングリストコメント)
                    if (isset($conditions['picking_comment_flg']) && strlen($conditions['picking_comment_flg']) > 0) {
                        $pickingComments = explode(',', $conditions['picking_comment_flg']);
                        if(count($pickingComments) == 1) {
                            if($pickingComments[0] == 1) {
                                $subQuery->where('picking_comment', '<>', '');
                                $subQuery->whereNotNull('picking_comment');
                            }
                        }
                    }

                    //ピッキングリストコメント
                    //(出荷基本 ピッキングリストコメント)
                    if (isset($conditions['picking_comment']) && strlen($conditions['picking_comment']) > 0) {
                        $subQuery->where('picking_comment', 'like', '%'. addcslashes($conditions['picking_comment'], '%_\\'). '%');
                    }

                    //                    //配送倉庫
                    //                    if (isset($conditions['m_warehouse_id']) && strlen($conditions['m_warehouse_id']) > 0)
                    //                    {
                    //                        $subQuery->where('m_warehouse_id', '=', $conditions['m_warehouse_id']);
                    //                    }

                    //温度帯
                    // if (isset($conditions['temperature_zone']) && strlen($conditions['temperature_zone']) > 0)
                    // {
                    //     $subQuery->whereIn('temperature_zone', explode(',', $conditions['temperature_zone']));
                    // }
                })->get();
            }

            //配送日の有無(なし)
            if (isset($conditions['deli_decision_date_flg']) && strlen($conditions['deli_decision_date_flg']) > 0 && strpos($conditions['deli_decision_date_flg'], '0') !== false) {
                $deliDecisionDates = explode(',', $conditions['deli_decision_date_flg']);
                if(count($deliDecisionDates) == 1) {
                    if($deliDecisionDates[0] == 0) {
                        $query->whereHas('deliHdr', function ($subQuery) use ($conditions) {
                            $subQuery->where('deli_decision_date', '<>', '0000-00-00');
                            $subQuery->whereNotNull('deli_decision_date');
                        })->get();
                    }
                }
            }

            //ピッキングコメントの有無(なし)
            //(出荷基本 ピッキングリストコメント)
            if (isset($conditions['picking_comment_flg']) && strlen($conditions['picking_comment_flg']) > 0 && strpos($conditions['picking_comment_flg'], '0') !== false) {
                $pickingComments = explode(',', $conditions['picking_comment_flg']);
                if(count($pickingComments) == 1) {
                    if($pickingComments[0] == 0) {
                        $query->whereHas('deliHdr', function ($subQuery) use ($conditions) {
                            $subQuery->where('picking_comment', '<>', '');
                            $subQuery->whereNotNull('picking_comment');
                        })->get();
                    }
                }
            }

            // メールテンプレートID
            if (isset($conditions['not_send_m_email_templates_id']) && strlen($conditions['not_send_m_email_templates_id']) > 0) {
                $query->wherehas('mailSendHistories', function ($subQuery) use ($conditions) {
                    $subQuery->whereIn('m_email_templates_id', explode(',', $conditions['not_send_m_email_templates_id']));
                })->get();
            }

            //見積フラグ
            if (isset($conditions['estimate_flg']) && strlen($conditions['estimate_flg']) > 0) {
                $query->whereIn('estimate_flg', explode(',', $conditions['estimate_flg']));
            }

            //領収書区分
            if (isset($conditions['receipt_type']) && strlen($conditions['receipt_type']) > 0) {
                $query->whereIn('receipt_type', explode(',', $conditions['receipt_type']));
            }

            // 販売窓口
            if (isset($conditions['sales_store']) && strlen($conditions['sales_store']) > 0) {
                $query->whereIn('sales_store', explode(',', $conditions['sales_store']));
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


    /**
     * 補足情報から追加で検索条件を設定する
     */
    protected function setOptions($query, $options): Builder
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
        if (!isset($options['sorts'])) {
            $options['sorts'] = $this->defaultSorts;

        }
        if(is_array($options['sorts'])) {
            foreach($options['sorts'] as $column => $direction) {
                // $options['join_table'] 配列に 't_order_destination' が含まれている場合
                if (!empty($options['join_table']) && is_array($options['join_table']) && in_array('t_order_destination', $options['join_table'])) {
                    if (Schema::hasColumn($this->hdrTable, $column)) {
                        // 受注基本にカラムがある場合
                        $query->orderBy($this->hdrTable . '.' . $column, $direction);
                    } elseif (Schema::hasColumn($this->destTable, $column)) {
                        // 受注基本に無く受注配送先にカラムがある場合
                        $query->orderBy($this->destTable . '.' . $column, $direction);
                    }
                } else {
                    $query->orderBy($column, $direction);
                }
            }
        } else {
            // $options['join_table'] 配列に 't_order_destination' が含まれている場合
            if (!empty($options['join_table']) && is_array($options['join_table']) && in_array('t_order_destination', $options['join_table'])) {
                if (Schema::hasColumn($this->hdrTable, $options['sorts'])) {
                    // 受注基本にカラムがある場合
                    $query->orderBy($this->hdrTable . '.' . $options['sorts'], 'asc');
                } elseif (Schema::hasColumn($this->destTable, $options['sorts'])) {
                    // 受注基本に無く受注配送先にカラムがある場合
                    $query->orderBy($this->destTable . '.' . $options['sorts'], 'asc');
                }
            } else {
                $query->orderBy($options['sorts'], 'asc');
            }
        }

        return $query;
    }

    // 背景色と警告情報を追加
    protected function addWarningInfo($item)
    {
        // 各列の背景色
        $item['color_class'] = '';
        if (!empty($item['progress_type']) && isset($this->progressTypeColorClass[$item['progress_type']])) {
            $item['color_class'] = $this->progressTypeColorClass[$item['progress_type']];
        }
        // 警告がある場合
        if(!empty($item['alert_order_flg'])) {
            $item['order_alert'] = '警告';
            $item['order_alert_class'] = 'font-FF0000';
        } else {
            $item['order_alert'] = '';
            $item['order_alert_class'] = '';
        }
        return $item;
    }

    private function setWhereConditionOrderDate(Builder $query, Carbon $orderDatetime)
    {
        $query->whereRaw('DATE_FORMAT(order_datetime, "%Y-%m-%d") = ?', $orderDatetime->format('Y-m-d'));
    }
}
