<?php

namespace App\Models\Order\Base;

use App\Models\Cc\Base\CustomerModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OrderModel extends Model
{
    /**
     * テーブル名称
     *
     * @var string
     */
    protected $table = 't_order_hdr';

    /**
     * テーブルの主キー
     *
     * @var string
     */
    protected $primaryKey = 't_order_hdr_id';

    /**
     * 受注ヘッダ以外で使用するテーブル名称
     */
    protected $orderDtlTableName = 't_order_dtl';
    protected $orderDtlSkuTableName = 't_order_dtl_sku';
    protected $orderDestinationTableName = 't_order_destination';
    protected $orderTagTableName = 't_order_tag';
    protected $deliHdrTableName = 't_deli_hdr';
    protected $custTableName = 'm_cust';
    protected $mailSendHistoryTable = 't_mail_send_history';
    protected $masterOrderTagTableName = 'm_order_tag';

    /**
     * m_userテーブルをjoinするか
     * (デフォルトはするが、しない場合のみfalseを設定する)
     *
     * @var bool
     */
    protected $joinUser = false;

    /**
     * お届け先の項目
     */
    protected $orderDestinationColumn = [
        't_order_destination_id',
        't_order_hdr_id',
        'order_destination_seq',
        'destination_alter_flg',
        'destination_tel',
        'destination_postal',
        'destination_address1',
        'destination_address2',
        'destination_address3',
        'destination_address4',
        'destination_company_name',
        'destination_division_name',
        'destination_name_kana',
        'destination_name',
        'deli_hope_date',
        'deli_hope_time_name',
        'deli_hope_time_cd',
        'm_delivery_type_id',
        'm_delivery_time_hope_id',
        'shipping_fee',
        'payment_fee',
        'wrapping_fee',
        'deli_plan_date',
        'area_cd',
        'gift_message',
        'gift_wrapping',
        'nosi_type',
        'nosi_name',
        'invoice_comment',
        'picking_comment',
        'partial_deli_flg',
        'entry_operator_id',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
    ];

    /**
     * お届け先ごとの受注明細の項目
     */
    protected $orderDtlColumn = [
        't_order_dtl_id',
        't_order_hdr_id',
        't_order_destination_id',
        'order_destination_seq',
        'order_dtl_seq',
        'ecs_id',
        'sell_id',
        'sell_cd',
        'sell_name',
        'sell_option',
        'order_sell_price',
        'tax_rate',
        'order_cost',
        'order_sell_vol',
        'order_time_sell_vol',
        'order_return_vol',
        'reservation_date',
        'deli_instruct_date',
        'deli_decision_date',
        't_deli_hdr_id',
        'bundle_from_order_id',
        'bundle_from_order_dtl_id',
        'order_dtl_coupon_id',
        'order_dtl_coupon_price',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
        'cancel_operator_id',
        'cancel_timestamp',
    ];

    /**
     * お届け先ごとの受注明細SKUの項目
     */
    protected $orderDtlSkuColumn = [
        't_order_dtl_sku_id',
        'm_account_id',
        't_order_hdr_id',
        't_order_destination_id',
        'order_destination_seq',
        't_order_dtl_id',
        'order_dtl_seq',
        'ecs_id',
        'sell_cd',
        'order_sell_vol',
        'item_id',
        'item_cd',
        'item_vol',
        'temp_reservation_flg',
        'm_warehouse_id',
        'reservation_date',
        'deli_instruct_date',
        'deli_decision_date',
        't_deli_hdr_id',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
        'cancel_operator_id',
        'cancel_timestamp',
    ];

    /**
     * 受注タグ情報の項目
     */
    protected $orderTagColumn = [
        't_order_tag_id',
        't_order_hdr_id',
        'm_order_tag_id',
        'auto_self_flg',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
        'cancel_operator_id',
        'cancel_timestamp',
    ];

    /**
     * 進捗区分変更履歴の項目
     */
    protected $progressUpdateHistoryColumn = [
        't_progress_update_history_id',
        'progress_type_from',
        'progress_type_to',
        'entry_operator_id',
        'entry_timestamp',
    ];

    /**
     * 決済履歴の項目
     */
    protected $settlementHistoryColumn = [
        't_settlement_history_id',
        'settlement_target',
        'settlement_type',
        'settlement_title',
        'settlement_note',
        'payment_transaction_id',
        'settlement_status',
        'settlement_error_cd',
        'settlement_error_message',
        'settlement_timestamp',
    ];

    /**
     * 連携履歴の項目
     */
    protected $cooperationHistoryColumn = [
        't_cooperation_history_id',
        'm_account_id',
        't_order_hdr_id',
        'cooperation_target',
        'cooperation_type',
        'cooperation_title',
        'cooperation_note',
        'cooperation_status',
        'cooperation_timestamp',
        'cooperation_error_cd',
        'cooperation_error_message',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
    ];

    /**
     * メール送信履歴の項目
     */
    protected $mailSendHistoryColumn = [
        't_mail_send_history_id',
        'mail_send_status',
        'mail_send_timestamp',
        'mail_title',
        'm_email_templates_id',
        'mail_text',
        'entry_operator_id',
    ];

    /**
     * 帳票出力履歴の項目
     */
    protected $reportOutputHistoryColumn = [
        't_report_output_history_id',
        't_order_hdr_id',
        't_deli_hdr_id',
        'output_timestamp',
        'report_type',
        'output_form',
        'entry_operator_id',
    ];

    /**
     * 出荷情報の項目
     */
    protected $deliHdrColumn = [
        'deli_package_vol',
        'invoice_num1',
        'invoice_num2',
        'invoice_num3',
        'invoice_num4',
        'invoice_num5',
    ];

    /**
     * 受注送付先とのリレーション
     */
    public function orderDestination()
    {
        return $this->hasMany(OrderDestinationModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

     /**
      * 受注明細とのリレーション
      */
    public function orderDetails()
    {
        return $this->hasMany(OrderDestinationModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

     /**
      * 注文主顧客とのリレーション
      */
    public function orderCustomer()
    {
        return $this->belongsTo(CustomerModel::class, 'm_cust_id', 'm_cust_id');
    }
    /**
     * 参照先のデータベースがグローバルなのか、ローカルなのか
     * (デフォルトはグローバルへ接続)
     *
     * @var string
     */
    // protected $connection = 'local';

    /**
     * Where条件の追加
     *
     * @param $searchInfo array
     */
    public function addWhere($searchInfo)
    {
        if(!empty($searchInfo)) {
            // 進捗区分
            if (isset($searchInfo['progress_type']) && strlen($searchInfo['progress_type']) > 0) {
                $this->dbSelect->whereIn('progress_type', explode(',', $searchInfo['progress_type']));
            }

            // 進捗区分自動手動
            if (isset($searchInfo['progress_type_auto_self']) && strlen($searchInfo['progress_type_auto_self']) > 0) {
                $this->setWhereOrTypeColumn($searchInfo['progress_type_auto_self'], 'progress_type_self_change');
            }

            // 進捗区分変更日時from
            if (isset($searchInfo['progress_update_datetime_from']) && strlen($searchInfo['progress_update_datetime_from']) > 0) {
                $this->dbSelect->where('progress_update_datetime', '>=', $searchInfo['progress_update_datetime_from']);
            }

            // 進捗区分変更日時to
            if (isset($searchInfo['progress_update_datetime_to']) && strlen($searchInfo['progress_update_datetime_to']) > 0) {
                $this->dbSelect->where('progress_update_datetime', '<=', $searchInfo['progress_update_datetime_to']);
            }

            // 要注意顧客
            if (isset($searchInfo['alert_cust_check_type']) && strlen($searchInfo['alert_cust_check_type']) > 0) {
                $this->dbSelect->whereIn('alert_cust_check_type', explode(',', $searchInfo['alert_cust_check_type']));
            }

            //住所エラー
            if (isset($searchInfo['address_check_type']) && strlen($searchInfo['address_check_type']) > 0) {
                $this->dbSelect->whereIn('address_check_type', explode(',', $searchInfo['address_check_type']));
            }

            //配達指定日エラー
            if (isset($searchInfo['deli_hope_date_check_type']) && strlen($searchInfo['deli_hope_date_check_type']) > 0) {
                $this->dbSelect->whereIn('deli_hope_date_check_type', explode(',', $searchInfo['deli_hope_date_check_type']));
            }

            //与信区分
            if (isset($searchInfo['credit_type']) && strlen($searchInfo['credit_type']) > 0) {
                $this->dbSelect->whereIn('credit_type', explode(',', $searchInfo['credit_type']));
            }

            //引当区分
            if (isset($searchInfo['reservation_type']) && strlen($searchInfo['reservation_type']) > 0) {
                $this->dbSelect->whereIn('reservation_type', explode(',', $searchInfo['reservation_type']));
            }

            //入金区分
            if (isset($searchInfo['payment_type']) && strlen($searchInfo['payment_type']) > 0) {
                $this->dbSelect->whereIn('payment_type', explode(',', $searchInfo['payment_type']));
            }

            //出荷指示区分
            if (isset($searchInfo['deli_instruct_type']) && strlen($searchInfo['deli_instruct_type']) > 0) {
                $this->dbSelect->whereIn('deli_instruct_type', explode(',', $searchInfo['deli_instruct_type']));
            }

            //出荷確定区分
            if (isset($searchInfo['deli_decision_type']) && strlen($searchInfo['deli_decision_type']) > 0) {
                $this->dbSelect->whereIn('deli_decision_type', explode(',', $searchInfo['deli_decision_type']));
            }
            //決済ステータス
            if (isset($searchInfo['settlement_sales_type']) && strlen($searchInfo['settlement_sales_type']) > 0) {
                $this->dbSelect->whereIn('settlement_sales_type', explode(',', $searchInfo['settlement_sales_type']));
            }

            //決済ステータス
            if (isset($searchInfo['disp_settlement_sales_type']) && strlen($searchInfo['disp_settlement_sales_type']) > 0) {
                $this->dbSelect->whereIn('sales_status_type', explode(',', $searchInfo['disp_settlement_sales_type']));
            }

            //売上ステータス反映区分
            if (isset($searchInfo['sales_status_type']) && strlen($searchInfo['sales_status_type']) > 0) {
                $this->dbSelect->whereIn('sales_status_type', explode(',', $searchInfo['sales_status_type']));
            }

            if ((isset($searchInfo['order_datetime_from']) && strlen($searchInfo['order_datetime_from']) > 0)
                || (isset($searchInfo['order_datetime_to']) && strlen($searchInfo['order_datetime_to']) > 0)) {
                //受注日時FROM
                if (isset($searchInfo['order_datetime_from']) && strlen($searchInfo['order_datetime_from']) > 0) {
                    $this->dbSelect->where('order_datetime', '>=', $searchInfo['order_datetime_from']);
                }

                //受注日時TO
                if (isset($searchInfo['order_datetime_to']) && strlen($searchInfo['order_datetime_to']) > 0) {
                    $this->dbSelect->where('order_datetime', '<=', $searchInfo['order_datetime_to']);
                }
            } elseif(isset($searchInfo['display_period']) && strlen($searchInfo['display_period']) > 0) {

                if($searchInfo['display_period'] != 9) {
                    //表示期間
                    $now = Carbon::now();

                    $this->dbSelect->where('order_datetime', '<=', $now->format('Y-m-d H:i:s'));

                    $dateFrom = Carbon::now();

                    $dateFrom->setWeekStartsAt(Carbon::MONDAY);

                    switch($searchInfo['display_period']) {
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
                            $dateFrom = $now->startOfWeek();
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
                    if (isset($searchInfo['order_time_from']) && strlen($searchInfo['order_time_from']) > 0) {
                        $dateFrom = $dateFrom->format('Y-m-d'). ' '. $searchInfo['order_time_from'];
                    } else {
                        $dateFrom = $dateFrom->format('Y-m-d');
                    }


                    $this->dbSelect->where('order_datetime', '>=', $dateFrom);
                }
            }

            // 支払方法
            if (isset($searchInfo['m_payment_types_id']) && strlen($searchInfo['m_payment_types_id']) > 0) {
                $this->dbSelect->whereIn($this->table. '.m_payment_types_id', explode(',', $searchInfo['m_payment_types_id']));
            }

            //受注ID
            if (isset($searchInfo['t_order_hdr_id']) && strlen($searchInfo['t_order_hdr_id']) > 0) {
                $this->dbSelect->whereIn($this->table. '.t_order_hdr_id', explode(',', $searchInfo['t_order_hdr_id']));
            }

            //即日配送
            if (isset($searchInfo['immediately_deli_flg']) && strlen($searchInfo['immediately_deli_flg']) > 0) {
                $this->setWhereOrTypeColumn($searchInfo['immediately_deli_flg'], 'immediately_deli_flg');
            }

            //楽天スーパーDEAL
            if (isset($searchInfo['rakuten_super_deal_flg']) && strlen($searchInfo['rakuten_super_deal_flg']) > 0) {
                $this->setWhereOrTypeColumn($searchInfo['rakuten_super_deal_flg'], 'rakuten_super_deal_flg');
            }

            //同梱
            if (isset($searchInfo['bundle']) && strlen($searchInfo['bundle']) > 0) {
                if($searchInfo['bundle'] == 1) {
                    $this->dbSelect->whereRaw("IFNULL(bundle_source_ids, '') <> ''");
                }
            }

            //ECサイト
            if (isset($searchInfo['m_ecs_id']) && strlen($searchInfo['m_ecs_id']) > 0) {
                $this->dbSelect->whereIn('m_ecs_id', explode(',', $searchInfo['m_ecs_id']));
            }

            //ECサイト注文ID
            if (isset($searchInfo['ec_order_num']) && strlen($searchInfo['ec_order_num']) > 0) {
                $this->dbSelect->where('ec_order_num', '=', $searchInfo['ec_order_num']);
            }

            //リピート注文
            if (isset($searchInfo['repeat_order']) && strlen($searchInfo['repeat_order']) > 0) {
                $this->dbSelect->whereIn('repeat_flg', explode(',', $searchInfo['repeat_order']));
            }

            //受注担当者
            if (isset($searchInfo['order_operator_id']) && strlen($searchInfo['order_operator_id']) > 0) {
                $this->dbSelect->where('order_operator_id', '=', $searchInfo['order_operator_id']);
            }

            //最終更新担当者
            if (isset($searchInfo['update_operator_id']) && strlen($searchInfo['update_operator_id']) > 0) {
                $this->dbSelect->where($this->table. '.update_operator_id', '=', $searchInfo['update_operator_id']);
            }

            //受注方法
            if (isset($searchInfo['order_type']) && strlen($searchInfo['order_type']) > 0) {
                $this->dbSelect->whereIN('order_type', explode(',', $searchInfo['order_type']));
            }

            //ギフト
            if (isset($searchInfo['gift_flg']) && strlen($searchInfo['gift_flg']) > 0) {
                $this->dbSelect->whereIn('gift_flg', explode(',', $searchInfo['gift_flg']));
            }

            //警告注文
            if (isset($searchInfo['alert_order_flg']) && strlen($searchInfo['alert_order_flg']) > 0) {
                $this->setWhereOrTypeColumn($searchInfo['alert_order_flg'], 'alert_order_flg');
            }

            //合計金額FROM
            if (isset($searchInfo['total_price_from']) && strlen($searchInfo['total_price_from']) > 0) {
                $this->dbSelect->where('sell_total_price', '>=', $searchInfo['total_price_from']);
            }

            //合計金額TO
            if (isset($searchInfo['total_price_to']) && strlen($searchInfo['total_price_to']) > 0) {
                $this->dbSelect->where('sell_total_price', '<=', $searchInfo['total_price_to']);
            }

            //請求金額FROM
            if (isset($searchInfo['order_total_price_from']) && strlen($searchInfo['order_total_price_from']) > 0) {
                $this->dbSelect->where('order_total_price', '>=', $searchInfo['order_total_price_from']);
            }

            //請求金額TO
            if (isset($searchInfo['order_total_price_to']) && strlen($searchInfo['order_total_price_to']) > 0) {
                $this->dbSelect->where('order_total_price', '<=', $searchInfo['order_total_price_to']);
            }

            //送料FROM
            if (isset($searchInfo['shipping_fee_from']) && strlen($searchInfo['shipping_fee_from']) > 0) {
                $this->dbSelect->where('shipping_fee', '>=', $searchInfo['shipping_fee_from']);
            }

            //送料TO
            if (isset($searchInfo['shipping_fee_to']) && strlen($searchInfo['shipping_fee_to']) > 0) {
                $this->dbSelect->where('shipping_fee', '<=', $searchInfo['shipping_fee_to']);
            }

            //備考の有無
            if (isset($searchInfo['order_comment_flg']) && strlen($searchInfo['order_comment_flg']) > 0) {
                $orderCommentFlg = explode(',', $searchInfo['order_comment_flg']);

                if(count($orderCommentFlg) == 1) {
                    if($orderCommentFlg[0] == 1) {
                        $this->dbSelect->whereRaw("IFNULL(order_comment, '') <> '' ");
                    } elseif($orderCommentFlg[0] == 0) {
                        $this->dbSelect->whereRaw("IFNULL(order_comment, '') = '' ");
                    }
                }
            }

            //備考
            if (isset($searchInfo['order_comment']) && strlen($searchInfo['order_comment']) > 0) {
                if(isset($searchInfo['order_comment_search_flg']) && strlen($searchInfo['order_comment_search_flg']) > 0) {
                    if($searchInfo['order_comment_search_flg'] == 1) {
                        $this->dbSelect->where('order_comment', 'like', '%'. $searchInfo['order_comment']. '%');
                    } else {
                        $this->dbSelect->where('order_comment', 'like', $searchInfo['order_comment']. '%');
                    }
                } else {
                    $this->dbSelect->where('order_comment', 'like', $searchInfo['order_comment']. '%');
                }
            }

            //社内メモの有無
            if (isset($searchInfo['operator_comment_flg']) && strlen($searchInfo['operator_comment_flg']) > 0) {
                $operatorCommentFlg = explode(',', $searchInfo['operator_comment_flg']);

                if(count($operatorCommentFlg) == 1) {
                    if($operatorCommentFlg[0] == 1) {
                        $this->dbSelect->whereRaw("IFNULL(operator_comment, '') <> '' ");
                    } elseif($operatorCommentFlg[0] == 0) {
                        $this->dbSelect->whereRaw("IFNULL(operator_comment, '') = '' ");
                    }
                }
            }

            //社内メモ
            if (isset($searchInfo['operator_comment']) && strlen($searchInfo['operator_comment']) > 0) {
                if(isset($searchInfo['operator_comment_search_flg']) && strlen($searchInfo['operator_comment_search_flg']) > 0) {
                    if($searchInfo['operator_comment_search_flg'] == 1) {
                        $this->dbSelect->where('operator_comment', 'like', '%'. $searchInfo['operator_comment']. '%');
                    } else {
                        $this->dbSelect->where('operator_comment', 'like', $searchInfo['operator_comment']. '%');
                    }
                } else {
                    $this->dbSelect->where('operator_comment', 'like', $searchInfo['operator_comment']. '%');
                }
            }

            //受注キャンセル日時FROM
            if (isset($searchInfo['cancel_datetime_from']) && strlen($searchInfo['cancel_datetime_from']) > 0) {
                $this->dbSelect->where('cancel_timestamp', '>=', $searchInfo['cancel_datetime_from']);
            }

            //受注キャンセル日時TO
            if (isset($searchInfo['cancel_datetime_to']) && strlen($searchInfo['cancel_datetime_to']) > 0) {
                $this->dbSelect->where('cancel_timestamp', '<=', $searchInfo['cancel_datetime_to']);
                $this->dbSelect->whereRaw("IFNULL (cancel_timestamp, '0000-00-00 00:00:00.000000') <> '0000-00-00 00:00:00.000000'");
            }

            //領収証最終出力日時FROM
            if (isset($searchInfo['receipt_datetime_from']) && strlen($searchInfo['receipt_datetime_from']) > 0) {
                $this->dbSelect->where('last_receipt_datetime', '>=', $searchInfo['receipt_datetime_from']);
            }

            //領収証最終出力日時TO
            if (isset($searchInfo['receipt_datetime_to']) && strlen($searchInfo['receipt_datetime_to']) > 0) {
                $this->dbSelect->where('last_receipt_datetime', '<=', $searchInfo['receipt_datetime_to']);
            }

            //受注タグ(含む)
            if (isset($searchInfo['order_tags_include']) && strlen($searchInfo['order_tags_include']) > 0) {
                // (含むのみチェックされているものを抽出)
                $includeTags = explode(',', $searchInfo['order_tags_include']);
                if (isset($searchInfo['order_tags_exclude']) && strlen($searchInfo['order_tags_exclude']) > 0) {
                    $includeTags = array_diff(explode(',', $searchInfo['order_tags_include']), explode(',', $searchInfo['order_tags_exclude']));
                }
                if (count($includeTags) > 0) {
                    $this->dbSelect->whereExists(function ($query) use ($includeTags) {
                        $existsTableName = $this->orderTagTableName;
                        $query->select('*')->from($existsTableName);
                        $query->whereRaw("{$this->table}.t_order_hdr_id = {$existsTableName}.t_order_hdr_id");

                        $query->whereIn("{$existsTableName}.m_order_tag_id", $includeTags);
                        $query->whereRaw("IFNULL({$existsTableName}.cancel_operator_id, 0) = 0");
                    });
                }
            }

            // 受注タグ(含まない)
            if (isset($searchInfo['order_tags_exclude']) && strlen($searchInfo['order_tags_exclude']) > 0) {
                // (含まないのみチェックされているものを抽出)
                $excludeTags = explode(',', $searchInfo['order_tags_exclude']);
                if (isset($searchInfo['order_tags_include']) && strlen($searchInfo['order_tags_include']) > 0) {
                    $excludeTags = array_diff(explode(',', $searchInfo['order_tags_exclude']), explode(',', $searchInfo['order_tags_include']));
                }
                if (count($excludeTags) > 0) {
                    $this->dbSelect->whereNotExists(function ($query) use ($excludeTags) {
                        $existsTableName = $this->orderTagTableName;
                        $query->select('*')->from($existsTableName);
                        $query->whereRaw("{$this->table}.t_order_hdr_id = {$existsTableName}.t_order_hdr_id");

                        $query->whereIn("{$existsTableName}.m_order_tag_id", $excludeTags);
                        $query->whereRaw("IFNULL({$existsTableName}.cancel_operator_id, 0) = 0");
                    });
                }
            }

            //電話番号・FAX
            if (isset($searchInfo['tel_fax']) && strlen($searchInfo['tel_fax']) > 0) {
                if(isset($searchInfo['tel_fax_search_flg']) && strlen($searchInfo['tel_fax_search_flg']) > 0 && $searchInfo['tel_fax_search_flg'] == 1) {
                    $this->dbSelect->where(function ($query) use ($searchInfo) {
                        $query->orWhere('order_tel1', 'like', $searchInfo['tel_fax']. '%');
                        $query->orWhere('order_tel2', 'like', $searchInfo['tel_fax']. '%');
                        $query->orWhere('order_fax', 'like', $searchInfo['tel_fax']. '%');
                    });
                } else {
                    $this->dbSelect->where(function ($query) use ($searchInfo) {
                        $query->orWhere('order_tel1', '=', $searchInfo['tel_fax']);
                        $query->orWhere('order_tel2', '=', $searchInfo['tel_fax']);
                        $query->orWhere('order_fax', '=', $searchInfo['tel_fax']);
                    });
                }
            }

            //注文者氏名・カナ氏名
            if (isset($searchInfo['order_name']) && strlen($searchInfo['order_name']) > 0) {
                $orderName = str_replace('　', '', str_replace(' ', '', $searchInfo['order_name']));
                if(isset($searchInfo['order_name_search_flg']) && strlen($searchInfo['order_name_search_flg']) > 0 && $searchInfo['order_name_search_flg'] == 1) {
                    $this->dbSelect->where(function ($query) use ($searchInfo, $orderName) {
                        $query->orWhere('gen_search_order_name', 'like', '%'. $orderName. '%');
                        $query->orWhere('gen_search_order_name_kana', 'like', '%'. $orderName. '%');
                    });
                } else {
                    $this->dbSelect->where(function ($query) use ($searchInfo, $orderName) {
                        $query->orWhere('gen_search_order_name', 'like', $orderName. '%');
                        $query->orWhere('gen_search_order_name_kana', 'like', $orderName. '%');
                    });
                }
            }

            //メールアドレス
            if (isset($searchInfo['order_email']) && strlen($searchInfo['order_email']) > 0) {
                if(isset($searchInfo['order_email_search_flg']) && strlen($searchInfo['order_email_search_flg']) > 0 && $searchInfo['order_email_search_flg'] == 1) {
                    $this->dbSelect->where(function ($query) use ($searchInfo) {
                        $query->orWhere('order_email1', 'like', $searchInfo['order_email']. '%');
                        $query->orWhere('order_email2', 'like', $searchInfo['order_email']. '%');
                    });
                } else {
                    $this->dbSelect->where(function ($query) use ($searchInfo) {
                        $query->orWhere('order_email1', '=', $searchInfo['order_email']);
                        $query->orWhere('order_email2', '=', $searchInfo['order_email']);
                    });
                }
            }

            //顧客ID
            if (isset($searchInfo['m_cust_id']) && strlen($searchInfo['m_cust_id']) > 0) {
                $this->dbSelect->where('m_cust_id', '=', $searchInfo['m_cust_id']);
            }

            // 顧客マスタ条件
            if((isset($searchInfo['m_cust_runk_id']) && strlen($searchInfo['m_cust_runk_id']) > 0)
                || (isset($searchInfo['cust_cd']) && strlen($searchInfo['cust_cd']) > 0)
                || (isset($searchInfo['alert_cust_type']) && strlen($searchInfo['alert_cust_type']) > 0)
            ) {
                $this->dbSelect->whereExists(function ($query) use ($searchInfo) {
                    $existsTableName = $this->custTableName;
                    $query->select('*')->from($existsTableName);
                    $query->whereRaw("{$this->table}.m_cust_id = {$existsTableName}.m_cust_id");


                    //顧客ランク
                    if (isset($searchInfo['m_cust_runk_id']) && strlen($searchInfo['m_cust_runk_id']) > 0) {
                        $query->whereIn('m_cust_runk_id', explode(',', $searchInfo['m_cust_runk_id']));
                    }

                    //顧客コード
                    if (isset($searchInfo['cust_cd']) && strlen($searchInfo['cust_cd']) > 0) {
                        $query->where('cust_cd', '=', $searchInfo['cust_cd']);
                    }


                    // 要注意顧客区分
                    if (isset($searchInfo['alert_cust_type']) && strlen($searchInfo['alert_cust_type']) > 0) {
                        $query->whereIn('alert_cust_type', explode(',', $searchInfo['alert_cust_type']));
                    }

                });
            }

            // 販売情報による商品条件
            if((isset($searchInfo['sell_cd']) && strlen($searchInfo['sell_cd']) > 0)
                || (isset($searchInfo['sell_name']) && strlen($searchInfo['sell_name']) > 0)
                || (isset($searchInfo['sell_option']) && strlen($searchInfo['sell_option']) > 0)
            ) {
                $this->dbSelect->whereExists(function ($query) use ($searchInfo) {
                    $existsTableName = $this->orderDtlTableName;
                    $query->select('*')->from($existsTableName);
                    $query->whereRaw("{$this->table}.t_order_hdr_id = {$existsTableName}.t_order_hdr_id");

                    //販売コード
                    if (isset($searchInfo['sell_cd']) && strlen($searchInfo['sell_cd']) > 0) {
                        $query->where($existsTableName.'.sell_cd', '=', $searchInfo['sell_cd']);
                    }

                    //販売名
                    if (isset($searchInfo['sell_name']) && strlen($searchInfo['sell_name']) > 0) {
                        if(isset($searchInfo['sell_name_search_flag']) && strlen($searchInfo['sell_name_search_flag']) > 0 && $searchInfo['sell_name_search_flag'] == 1) {
                            $query->where($existsTableName.'.sell_name', 'like', '%'. $searchInfo['sell_name']. '%');
                        } else {
                            $query->where($existsTableName.'.sell_name', 'like', $searchInfo['sell_name']. '%');
                        }
                    }

                    //項目選択肢
                    if (isset($searchInfo['sell_option']) && strlen($searchInfo['sell_option']) > 0) {
                        $query->where($existsTableName.'.sell_option', 'like', '%'. $searchInfo['sell_option']. '%');
                    }
                });
            }

            // 販売情報SKUによる商品条件
            if((isset($searchInfo['item_cd']) && strlen($searchInfo['item_cd']) > 0)
                || (isset($searchInfo['m_warehouse_id']) && strlen($searchInfo['m_warehouse_id']) > 0)
                || (isset($searchInfo['temperature_zone']) && strlen($searchInfo['temperature_zone']) > 0)
                || (isset($searchInfo['m_suppliers_id']) && strlen($searchInfo['m_suppliers_id']) > 0)
                || (isset($searchInfo['direct_deli_flg']) && strlen($searchInfo['direct_deli_flg']) > 0)
            ) {
                $this->dbSelect->whereExists(function ($query) use ($searchInfo) {
                    $existsTableName = $this->orderDtlSkuTableName;
                    $query->select('*')->from($existsTableName);
                    $query->whereRaw("{$this->table}.t_order_hdr_id = {$existsTableName}.t_order_hdr_id");

                    //商品コード
                    if (isset($searchInfo['item_cd']) && strlen($searchInfo['item_cd']) > 0) {
                        $query->where($existsTableName.'.item_cd', '=', $searchInfo['item_cd']);
                    }
                    //配送倉庫
                    if (isset($searchInfo['m_warehouse_id']) && strlen($searchInfo['m_warehouse_id']) > 0) {
                        $query->whereIn('m_warehouse_id', explode(',', $searchInfo['m_warehouse_id']));
                    }

                    //温度帯
                    if (isset($searchInfo['temperature_zone']) && strlen($searchInfo['temperature_zone']) > 0) {
                        $query->whereIn('temperature_type', explode(',', $searchInfo['temperature_zone']));
                    }

                    //仕入先コード
                    if (isset($searchInfo['m_suppliers_id']) && strlen($searchInfo['m_suppliers_id']) > 0) {
                        $query->where('m_supplier_id', '=', $searchInfo['m_suppliers_id']);
                    }

                    //直送
                    if (isset($searchInfo['direct_deli_flg']) && strlen($searchInfo['direct_deli_flg']) > 0) {
                        $query->whereIn('direct_delivery_type', explode(',', $searchInfo['direct_deli_flg']));
                    }
                });
            }


            //後払い.com注文ID
            if (isset($searchInfo['payment_transaction_id']) && strlen($searchInfo['payment_transaction_id']) > 0) {
                $this->dbSelect->where('payment_transaction_id', '=', $searchInfo['payment_transaction_id']);
            }

            //後払い.com決済ステータス
            if (isset($searchInfo['cb_credit_status']) && strlen($searchInfo['cb_credit_status']) > 0) {
                $this->dbSelect->whereIn('cb_credit_status', explode(',', $searchInfo['cb_credit_status']));
            }

            //後払い.com出荷ステータス
            if (isset($searchInfo['cb_deli_status']) && strlen($searchInfo['cb_deli_status']) > 0) {
                $this->dbSelect->whereIn('cb_deli_status', explode(',', $searchInfo['cb_deli_status']));
            }

            //後払い.com請求書送付ステータス
            if (isset($searchInfo['cb_billed_status']) && strlen($searchInfo['cb_billed_status']) > 0) {
                $this->dbSelect->whereIn('cb_billed_status', explode(',', $searchInfo['cb_billed_status']));
            }

            //請求書送付種別
            if (isset($searchInfo['cb_billed_type']) && strlen($searchInfo['cb_billed_type']) > 0) {
                $this->dbSelect->whereIn('cb_billed_type', explode(',', $searchInfo['cb_billed_type']));
            }

            //決済金額差異
            if (isset($searchInfo['payment_diff_flg']) && strlen($searchInfo['payment_diff_flg']) > 0) {
                if($searchInfo['payment_diff_flg'] == 1) {
                    $this->dbSelect->whereRaw('IFNULL(payment_price, 0) <> order_total_price');
                }
            }

            //入金日FROM
            if (isset($searchInfo['payment_date_from']) && strlen($searchInfo['payment_date_from']) > 0) {
                $this->dbSelect->where('payment_date', '>=', $searchInfo['payment_date_from']);
            }

            //入金日TO
            if (isset($searchInfo['payment_date_to']) && strlen($searchInfo['payment_date_to']) > 0) {
                $this->dbSelect->where('payment_date', '<=', $searchInfo['payment_date_to']);
                $this->dbSelect->whereRaw("payment_date <> '0000-00-00'");
            }

            //入金金額FROM
            if (isset($searchInfo['payment_price_from']) && strlen($searchInfo['payment_price_from']) > 0) {
                $this->dbSelect->where('payment_price', '>=', $searchInfo['payment_price_from']);
            }

            //入金金額TO
            if (isset($searchInfo['payment_price_to']) && strlen($searchInfo['payment_price_to']) > 0) {
                $this->dbSelect->where('payment_price', '<=', $searchInfo['payment_price_to']);
            }

            //複数配送先
            if (isset($searchInfo['multiple_deli_flg']) && strlen($searchInfo['multiple_deli_flg']) > 0) {
                $this->dbSelect->where('multiple_deli_flg', '=', $searchInfo['multiple_deli_flg']);
            }

            // お届け先関係
            if(
                (isset($searchInfo['deli_plan_date_from']) && strlen($searchInfo['deli_plan_date_from']) > 0)
                || (isset($searchInfo['deli_plan_date_to']) && strlen($searchInfo['deli_plan_date_to']) > 0)
                || (isset($searchInfo['deli_hope_date_from']) && strlen($searchInfo['deli_hope_date_from']) > 0)
                || (isset($searchInfo['deli_hope_date_to']) && strlen($searchInfo['deli_hope_date_to']) > 0)
                || (isset($searchInfo['order_deli_address_check_flag']) && strlen($searchInfo['order_deli_address_check_flag']) > 0 && $searchInfo['order_deli_address_check_flag'] == 1)
                || (isset($searchInfo['destination_address1']) && strlen($searchInfo['destination_address1']) > 0)
                || (isset($searchInfo['destination_postal']) && strlen($searchInfo['destination_postal']) > 0)
                || (isset($searchInfo['deli_hope_time_cd']) && strlen($searchInfo['deli_hope_time_cd']) > 0)
                || (isset($searchInfo['destination_address234']) && strlen($searchInfo['destination_address234']) > 0)
                || (isset($searchInfo['destination_name']) && strlen($searchInfo['destination_name']) > 0)
                || (isset($searchInfo['t_order_destinaton_id']) && strlen($searchInfo['t_order_destinaton_id']) > 0)
                || (isset($searchInfo['invoice_comment_flg']) && strlen($searchInfo['invoice_comment_flg']) > 0 && strpos($searchInfo['invoice_comment_flg'], '1') !== false)
                || (isset($searchInfo['invoice_comment']) && strlen($searchInfo['invoice_comment']) > 0)
                || (isset($searchInfo['multi_warehouse_flg']) && strlen($searchInfo['multi_warehouse_flg']) > 0)
                || (isset($searchInfo['m_deli_type_id']) && strlen($searchInfo['m_deli_type_id']) > 0)
            ) {
                $this->dbSelect->whereExists(function ($query) use ($searchInfo) {
                    $existsTableName = $this->orderDestinationTableName;
                    $query->select('*')->from($existsTableName);
                    $query->whereRaw("{$this->table}.t_order_hdr_id = {$existsTableName}.t_order_hdr_id");
                    $query->whereNull("{$this->table}.cancel_operator_id");

                    //出荷予定日FROM
                    if (isset($searchInfo['deli_plan_date_from']) && strlen($searchInfo['deli_plan_date_from']) > 0) {
                        $query->where($existsTableName. '.deli_plan_date', '>=', $searchInfo['deli_plan_date_from']);
                    }

                    //出荷予定日TO
                    if (isset($searchInfo['deli_plan_date_to']) && strlen($searchInfo['deli_plan_date_to']) > 0) {
                        $query->where($existsTableName. '.deli_plan_date', '<=', $searchInfo['deli_plan_date_to']);
                        $query->where($existsTableName. '.deli_plan_date', '<>', '0000-00-00');
                    }

                    //配送希望日FROM
                    if (isset($searchInfo['deli_hope_date_from']) && strlen($searchInfo['deli_hope_date_from']) > 0) {
                        $query->where($existsTableName.'.deli_hope_date', '>=', $searchInfo['deli_hope_date_from']);
                    }

                    //配送希望日TO
                    if (isset($searchInfo['deli_hope_date_to']) && strlen($searchInfo['deli_hope_date_to']) > 0) {
                        $query->where($existsTableName. '.deli_hope_date', '<=', $searchInfo['deli_hope_date_to']);
                        $query->where($existsTableName. '.deli_hope_date', '<>', '0000-00-00');
                    }

                    //配送希望時間帯
                    if (isset($searchInfo['deli_hope_time_cd']) && strlen($searchInfo['deli_hope_time_cd']) > 0) {
                        $query->whereIn($existsTableName. '.deli_hope_time_cd', explode(',', $searchInfo['deli_hope_time_cd']));
                    }

                    //配送先氏名・カナ氏名
                    if (isset($searchInfo['destination_name']) && strlen($searchInfo['destination_name']) > 0) {
                        $destinationName = '';
                        if(isset($searchInfo['destination_search_flag']) && strlen($searchInfo['destination_search_flag']) && $searchInfo['destination_search_flag'] == 1) {
                            $destinationName = '%'. $searchInfo['destination_name'] .'%';
                        } else {
                            $destinationName = $searchInfo['destination_name'] .'%';
                        }

                        if(!empty(str_replace('%', '', $destinationName))) {
                            $query->where(function ($query2) use ($destinationName, $existsTableName) {
                                $query2->orWhere($existsTableName. '.destination_name', 'like', $destinationName);
                                $query2->orWhere($existsTableName. '.destination_name_kana', 'like', $destinationName);
                            });
                        }
                    }

                    //注文・送付先不一致
                    if (isset($searchInfo['order_deli_address_check_flag']) && strlen($searchInfo['order_deli_address_check_flag']) > 0 && $searchInfo['order_deli_address_check_flag'] == 1) {
                        $query->whereRaw(
                            "CONCAT(IFNULL({$this->table}.order_address1, ''), IFNULL({$this->table}.order_address2, ''), IFNULL({$this->table}.order_address3, ''), IFNULL({$this->table}.order_address4, '')) ".
                            "<> CONCAT(IFNULL({$existsTableName}.destination_address1, ''), IFNULL({$existsTableName}.destination_address2, ''), IFNULL({$existsTableName}.destination_address3, ''), IFNULL({$existsTableName}.destination_address4, ''))"
                        );
                    }

                    //送付先都道府県
                    if (isset($searchInfo['destination_address1']) && strlen($searchInfo['destination_address1']) > 0) {
                        $query->whereIn($existsTableName. '.destination_address1', explode(',', $searchInfo['destination_address1']));
                    }

                    // 配送先郵便番号
                    if (isset($searchInfo['destination_postal']) && strlen($searchInfo['destination_postal']) > 0) {
                        $query->where($existsTableName. '.destination_postal', '=', $searchInfo['destination_postal']);
                    }

                    //送付先住所
                    if (isset($searchInfo['destination_address234']) && strlen($searchInfo['destination_address234']) > 0) {
                        $query->whereRaw("CONCAT(IFNULL({$existsTableName}.destination_address2, ''), IFNULL({$existsTableName}.destination_address3, ''), IFNULL({$existsTableName}.destination_address4, '')) like '{$searchInfo['destination_address234']}%'");
                    }

                    // お届け先ID
                    if (isset($searchInfo['t_order_destinaton_id']) && strlen($searchInfo['t_order_destinaton_id']) > 0) {
                        $query->whereIn($existsTableName. '.t_order_destinaton_id', explode(',', $searchInfo['delivery_postal']));
                    }

                    //送り状コメントの有無(あり)
                    if (isset($searchInfo['invoice_comment_flg']) && strlen($searchInfo['invoice_comment_flg']) > 0) {
                        $invoiceComments = explode(',', $searchInfo['invoice_comment_flg']);
                        if(count($invoiceComments) == 1) {
                            if($invoiceComments[0] == 1) {
                                $query->whereRaw("{$existsTableName}. invoice_comment <> '' AND {$existsTableName}. invoice_comment IS NOT NULL");
                            }
                        }
                    }

                    //送り状コメント
                    if (isset($searchInfo['invoice_comment']) && strlen($searchInfo['invoice_comment']) > 0) {
                        $query->where('invoice_comment', 'like', '%'. $searchInfo['invoice_comment']. '%');
                    }

                    // 複数倉庫引当フラグ
                    if(isset($searchInfo['multi_warehouse_flg']) && strlen($searchInfo['multi_warehouse_flg']) > 0) {

                        $query->where(function ($query2) use ($searchInfo, $existsTableName) {
                            // 検索値0はis nullに当てる
                            $multi_warehouse_flg = explode(',', $searchInfo['multi_warehouse_flg']);
                            $flgNull = \array_search('0', $multi_warehouse_flg);
                            if($flgNull !== false) {
                                $query2->orWhereNull("{$existsTableName}.multi_warehouse_flg");
                                $query2->orWhereIn($existsTableName. '.multi_warehouse_flg', $multi_warehouse_flg);
                            } else {
                                $query2->whereIn($existsTableName. '.multi_warehouse_flg', $multi_warehouse_flg);
                            }
                        });
                    }

                    //配送方法
                    if (isset($searchInfo['m_deli_type_id']) && strlen($searchInfo['m_deli_type_id']) > 0) {
                        $query->whereIn($existsTableName. '.m_delivery_type_id', explode(',', $searchInfo['m_deli_type_id']));
                    }
                });
            }

            // 配送先予定日関連
            if ((isset($searchInfo['deli_plan_date_nothing_flg']) && strlen($searchInfo['deli_plan_date_nothing_flg']) > 0 && $searchInfo['deli_plan_date_nothing_flg'] == 1)
                || (isset($searchInfo['deli_hope_date_nothing_flg']) && strlen($searchInfo['deli_hope_date_nothing_flg']) > 0 && $searchInfo['deli_hope_date_nothing_flg'] == 1)
            ) {
                $this->dbSelect->whereNotExists(function ($query) use ($searchInfo) {
                    $existsTableName = $this->orderDestinationTableName;
                    $query->select('*')->from($existsTableName);
                    $query->whereRaw("{$this->table}.t_order_hdr_id = {$existsTableName}.t_order_hdr_id");

                    //出荷予定日なし
                    if(isset($searchInfo['deli_plan_date_nothing_flg']) && strlen($searchInfo['deli_plan_date_nothing_flg']) > 0 && $searchInfo['deli_plan_date_nothing_flg'] == 1) {
                        $query->whereRaw("{$existsTableName}.deli_plan_date IS NOT NULL");
                        $query->whereRaw("{$existsTableName}.deli_plan_date <> '0000-00-00'");
                    }

                    //配送希望日なし
                    if (isset($searchInfo['deli_hope_date_nothing_flg']) && strlen($searchInfo['deli_hope_date_nothing_flg']) > 0 && $searchInfo['deli_hope_date_nothing_flg'] == 1) {
                        $query->whereRaw("{$existsTableName}.deli_hope_date IS NOT NULL");
                        $query->whereRaw("{$existsTableName}.deli_hope_date <> '0000-00-00'");
                    }
                });
            }

            //送り状コメントの有無(なし)
            if (isset($searchInfo['invoice_comment_flg']) && strlen($searchInfo['invoice_comment_flg']) > 0 && strpos($searchInfo['invoice_comment_flg'], '0') !== false) {
                $invoiceComments = explode(',', $searchInfo['invoice_comment_flg']);
                if(count($invoiceComments) == 1) {
                    if($invoiceComments[0] == 0) {
                        $this->dbSelect->whereNotExists(function ($query) use ($searchInfo) {
                            $existsTableName = $this->orderDestinationTableName;
                            $query->select('*')->from($existsTableName);
                            $query->whereRaw("{$this->table}.t_order_hdr_id = {$existsTableName}.t_order_hdr_id");
                            $query->whereRaw("{$existsTableName}. invoice_comment <> ''");
                            $query->whereRaw("{$existsTableName}. invoice_comment IS NOT NULL");
                        });
                    }
                }
            }


            // 配送関係
            if(
                (isset($searchInfo['t_deli_hdr_id']) && strlen($searchInfo['t_deli_hdr_id']) > 0)
                // || (isset($searchInfo['m_deli_type_id']) && strlen($searchInfo['m_deli_type_id']) > 0)
                || (isset($searchInfo['invoice_num']) && strlen($searchInfo['invoice_num']) > 0)
                || (isset($searchInfo['deli_decision_date_flg']) && strlen($searchInfo['deli_decision_date_flg']) > 0 && strpos($searchInfo['deli_decision_date_flg'], '1') !== false)
                || (isset($searchInfo['deli_decision_date_from']) && strlen($searchInfo['deli_decision_date_from']) > 0)
                || (isset($searchInfo['deli_decision_date_to']) && strlen($searchInfo['deli_decision_date_to']) > 0)
                || (isset($searchInfo['picking_comment_flg']) && strlen($searchInfo['picking_comment_flg']) > 0  && strpos($searchInfo['picking_comment_flg'], '1') !== false)
                || (isset($searchInfo['picking_comment']) && strlen($searchInfo['picking_comment']) > 0)
                // || (isset($searchInfo['m_warehouse_id']) && strlen($searchInfo['m_warehouse_id']) > 0)
                // || (isset($searchInfo['temperature_zone']) && strlen($searchInfo['temperature_zone']) > 0)
            ) {
                $this->dbSelect->whereExists(function ($query) use ($searchInfo) {
                    $existsTableName = $this->deliHdrTableName;
                    $query->select('*')->from($existsTableName);
                    $query->whereRaw("{$this->table}.t_order_hdr_id = {$existsTableName}.t_order_hdr_id");

                    //配送ID
                    if (isset($searchInfo['t_deli_hdr_id']) && strlen($searchInfo['t_deli_hdr_id']) > 0) {
                        $query->whereIn($existsTableName. '.t_deli_hdr_id', explode(',', $searchInfo['t_deli_hdr_id']));
                    }

                    // //配送方法
                    // if (isset($searchInfo['m_deli_type_id']) && strlen($searchInfo['m_deli_type_id']) > 0)
                    // {
                    // 	$query->where($existsTableName. '.m_deli_type_id', '=', $searchInfo['m_deli_type_id']);
                    // }

                    //送り状番号
                    if (isset($searchInfo['invoice_num']) && strlen($searchInfo['invoice_num']) > 0) {
                        $query->where(function ($query2) use ($searchInfo, $existsTableName) {
                            $query2->orWhere($existsTableName. '.invoice_num1', '=', $searchInfo['invoice_num']);
                            $query2->orWhere($existsTableName. '.invoice_num2', '=', $searchInfo['invoice_num']);
                            $query2->orWhere($existsTableName. '.invoice_num3', '=', $searchInfo['invoice_num']);
                            $query2->orWhere($existsTableName. '.invoice_num4', '=', $searchInfo['invoice_num']);
                            $query2->orWhere($existsTableName. '.invoice_num5', '=', $searchInfo['invoice_num']);
                        });
                    }

                    //配送日の有無(あり)
                    if (isset($searchInfo['deli_decision_date_flg']) && strlen($searchInfo['deli_decision_date_flg']) > 0) {
                        $deliDecisionDates = explode(',', $searchInfo['deli_decision_date_flg']);
                        if(count($deliDecisionDates) == 1) {
                            if($deliDecisionDates[0] == 1) {
                                $query->whereRaw("{$existsTableName}. deli_decision_date <> '0000-00-00' AND {$existsTableName}. deli_decision_date IS NOT NULL");
                            }
                        }
                    }

                    //配送日FROM
                    if (isset($searchInfo['deli_decision_date_from']) && strlen($searchInfo['deli_decision_date_from']) > 0) {
                        $query->where($existsTableName. '.deli_decision_date', '>=', $searchInfo['deli_decision_date_from']);
                    }

                    //配送日TO
                    if (isset($searchInfo['deli_decision_date_to']) && strlen($searchInfo['deli_decision_date_to']) > 0) {
                        $query->where($existsTableName. '.deli_decision_date', '<=', $searchInfo['deli_decision_date_to']);
                    }

                    //ピッキングコメントの有無(あり)
                    if (isset($searchInfo['picking_comment_flg']) && strlen($searchInfo['picking_comment_flg']) > 0) {
                        $pickingComments = explode(',', $searchInfo['picking_comment_flg']);
                        if(count($pickingComments) == 1) {
                            if($pickingComments[0] == 1) {
                                $query->whereRaw("{$existsTableName}. picking_comment <> '' AND {$existsTableName}. picking_comment IS NOT NULL");
                            }
                        }
                    }

                    //ピッキングコメント
                    if (isset($searchInfo['picking_comment']) && strlen($searchInfo['picking_comment']) > 0) {
                        $query->where('picking_comment', 'like', '%'. $searchInfo['picking_comment']. '%');
                    }

                    //					//配送倉庫
                    //					if (isset($searchInfo['m_warehouse_id']) && strlen($searchInfo['m_warehouse_id']) > 0)
                    //					{
                    //						$query->where('m_warehouse_id', '=', $searchInfo['m_warehouse_id']);
                    //					}

                    //温度帯
                    // if (isset($searchInfo['temperature_zone']) && strlen($searchInfo['temperature_zone']) > 0)
                    // {
                    // 	$query->whereIn('temperature_zone', explode(',', $searchInfo['temperature_zone']));
                    // }
                });
            }

            //配送日の有無(なし)
            if (isset($searchInfo['deli_decision_date_flg']) && strlen($searchInfo['deli_decision_date_flg']) > 0 && strpos($searchInfo['deli_decision_date_flg'], '0') !== false) {
                $deliDecisionDates = explode(',', $searchInfo['deli_decision_date_flg']);
                if(count($deliDecisionDates) == 1) {
                    if($deliDecisionDates[0] == 0) {
                        $this->dbSelect->whereNotExists(function ($query) use ($searchInfo) {
                            $existsTableName = $this->deliHdrTableName;
                            $query->select('*')->from($existsTableName);
                            $query->whereRaw("{$this->table}.t_order_hdr_id = {$existsTableName}.t_order_hdr_id");
                            $query->whereRaw("{$existsTableName}. deli_decision_date <> '0000-00-00'");
                            $query->whereRaw("{$existsTableName}. deli_decision_date IS NOT NULL");
                        });
                    }
                }
            }

            //ピッキングコメントの有無(なし)
            if (isset($searchInfo['picking_comment_flg']) && strlen($searchInfo['picking_comment_flg']) > 0 && strpos($searchInfo['picking_comment_flg'], '0') !== false) {
                $pickingComments = explode(',', $searchInfo['picking_comment_flg']);
                if(count($pickingComments) == 1) {
                    if($pickingComments[0] == 0) {
                        $this->dbSelect->whereNotExists(function ($query) use ($searchInfo) {
                            $existsTableName = $this->deliHdrTableName;
                            $query->select('*')->from($existsTableName);
                            $query->whereRaw("{$this->table}.t_order_hdr_id = {$existsTableName}.t_order_hdr_id");
                            $query->whereRaw("{$existsTableName}. picking_comment <> ''");
                            $query->whereRaw("{$existsTableName}. picking_comment IS NOT NULL");
                        });
                    }
                }
            }

            // メールテンプレートID
            if (isset($searchInfo['not_send_m_email_templates_id']) && strlen($searchInfo['not_send_m_email_templates_id']) > 0) {
                $this->dbSelect->whereNotExists(function ($query) use ($searchInfo) {
                    $existsTableName = $this->mailSendHistoryTable;
                    $query->select('*')->from($existsTableName);
                    $query->whereRaw("{$this->table}.t_order_hdr_id = {$existsTableName}.t_order_hdr_id");

                    $query->whereIn('m_email_templates_id', explode(',', $searchInfo['not_send_m_email_templates_id']));
                });
            }
        }
    }

    /**
     * 検索
     *
     * @param $request Request
     */
    public function getRows($request)
    {
        $searchInfo = $this->getSearchInfoFromRequest($request);

        $this->getDbSelect();

        $this->addSelectColumn();
        $this->setJoinTable(false);
        $this->setSelectLimit($request);

        $this->addWhere($searchInfo);
        $this->addQueryExtend($searchInfo);

        $this->setQueryOrder();

        $dbRow = $this->dbSelect->get();

        return $dbRow;
    }

    /**
     * 行数のカウント
     *
     * @param $request Request
     * @return array
     */
    public function getRowCount($request)
    {
        $searchInfo = $this->getSearchInfoFromRequest($request);

        $this->getDbSelect();

        $this->setJoinTable(true);
        $this->dbSelect->selectRaw('count(1) AS count');

        $this->addWhere($searchInfo);
        $this->addQueryExtend($searchInfo);

        $dbRow = $this->dbSelect->first();

        return $dbRow;
    }

    /**
     * 件数上限の取得
     */
    public function getRowLimits()
    {
        return $this->selectLimit;
    }

    /**
     * 数値項目で、複数条件を指定された場合の条件セット
     */
    protected function setWhereOrTypeColumn($columnValue, $columnName, $isNullZero = true, $tableName = '')
    {
        $selectTableName = !empty($tableName) ? $tableName : $this->table;

        $columnValues = explode(',', $columnValue);
        logger($columnValues);

        if(count($columnValues) == 1) {
            if($columnValue == 0 && $isNullZero) {
                $this->dbSelect->where(function ($query) use ($selectTableName, $columnName) {
                    $query->orWhere($selectTableName. '.'. $columnName, '=', 0);
                    $query->orWhereNull($selectTableName. '.'. $columnName);
                });
            } else {
                $this->dbSelect->where($selectTableName. '.'. $columnName, '=', $columnValue);
            }
        } elseif(count($columnValues) > 1) {
            $this->dbSelect->where(function ($query) use ($selectTableName, $columnName, $columnValues, $isNullZero) {
                foreach($columnValues as $cValue) {
                    if($cValue == 0 && $isNullZero) {
                        $query->orWhereNull($selectTableName. '.'. $columnName);
                    }
                    $query->orWhere($selectTableName. '.'. $columnName, '=', $cValue);
                }
            });
        }
    }

    /**
     * ORDER BYの条件
     */
    protected function setQueryOrder()
    {
        // 画面検索の一覧で出力する場合は受注IDの降順で検索する
        if($this->displayCsvFlag) {
            $this->dbSelect->orderBy($this->table. '.'. $this->primaryKey, 'desc');
        } else {
            parent::setQueryOrder();
        }
    }
}
