<?php

namespace App\Models\Order\Base;

use Illuminate\Support\Facades\DB;
use App\Models\Order\Base\OrderModel;

class OrderListModel extends OrderModel
{
    /**
     * INSERT UPDATEに使用するカラム
     *
     * @var array
     */
    protected $fillAble = [
        't_order_hdr_id',
        'ec_order_num',
        'm_cust_id',
        'order_operator_id',
        'order_type',
        'm_ecs_id',
        'order_tel1',
        'order_tel2',
        'order_fax',
        'order_email1',
        'order_email2',
        'order_postal',
        'order_address1',
        'order_address2',
        'order_address3',
        'order_address4',
        'order_corporate_name',
        'order_division_name',
        'order_name',
        'order_name_kana',
        'm_payment_types_id',
        'card_company',
        'card_holder',
        'card_pay_times',
        'alert_order_flg',
        'tax_rate',
        'sell_total_price',
        'discount',
        'shipping_fee',
        'payment_fee',
        'package_fee',
        'use_point',
        'use_coupon_store',
        'use_coupon_mall',
        'total_use_coupon',
        'order_total_price',
        'tax_price',
        'payment_type',
        'payment_date',
        'payment_price',
        'order_comment',
        'gift_flg',
        'immediately_deli_flg',
        'rakuten_super_deal_flg',
        'mall_member_id',
        'repeat_flg',
        'forced_deli_flg',
        'progress_type',
        'progress_type_self_change',
        'progress_update_operator_id',
        'progress_update_datetime',
        'comment_check_type',
        'comment_check_datetime',
        'alert_cust_check_type',
        'alert_cust_check_datetime',
        'address_check_type',
        'address_check_datetime',
        'deli_hope_date_check_type',
        'deli_hope_date_check_datetime',
        'credit_type',
        'credit_datetime',
        'payment_type',
        'payment_datetime',
        'reservation_type',
        'reservation_datetime',
        'deli_instruct_type',
        'deli_instruct_datetime',
        'deli_decision_type',
        'deli_decision_datetime',
        'settlement_sales_type',
        'settlement_sales_datetime',
        'sales_status_type',
        'sales_status_datetime',
        'bundle_order_id',
        'bundle_source_ids',
        'payment_transaction_id',
        'cb_billed_type',
        'cb_credit_status',
        'cb_deli_status',
        'cb_billed_status',
        'receipt_direction',
        'receipt_proviso',
        'last_receipt_datetime',
        'gen_search_order_name',
        'gen_search_order_name_kana',
        'order_datetime',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
        'cancel_operator_id',
        'cancel_timestamp',
        'cancel_type',
        'cancel_note',
    ];


    /**
     * 結合するテーブル
     *
     * @var array
     */
    protected $joinTables = [
        't_order_memo' => [
            'join_table_name'	=> 't_order_memo',
            'local_db_flag'		=> true,
            'join_rules'		=> [['base_table_column' => 't_order_hdr_id', 'join_table_column' => 't_order_hdr_id']],
            'select_columns'	=> ['operator_comment' => 'operator_comment'],
        ]
    ];

    /**
     * 追加取得データのセット
     * （配列の中に配列をセットする場合など）
     *
     * @param $iRow array
     * @return array
     */
    public function getExtendData($iRow)
    {
        //		$localDbName = $this->accountData['local_database_name'];
        $localDbName = $this->localDbName;

        // お届け先情報の取得
        $orderDestinationDb = DB::table($localDbName. '.'. $this->orderDestinationTableName);

        $orderDestinationDb->where('t_order_hdr_id', '=', $iRow['t_order_hdr_id']);
        $orderDestinationDb->orderBy('order_destination_seq');

        $odRows = $this->getArrayDbSelect($this->orderDestinationColumn, $orderDestinationDb);

        $orderDestinations = [];

        foreach ($odRows as $oDest) {
            $oDestRow = $oDest;

            // お届け先情報に基づく明細情報の取得
            $orderDtlDb = DB::table($localDbName. '.'. $this->orderDtlTableName);

            $orderDtlDb->leftJoin($localDbName. '.'. $this->deliHdrTableName, $localDbName. '.'. $this->orderDtlTableName. '.t_deli_hdr_id', '=', $localDbName. '.'. $this->deliHdrTableName. '.t_deli_hdr_id');

            $selectColumn = [];
            foreach($this->orderDtlColumn as $oDtlColumn) {
                $selectColumn[] = $this->orderDtlTableName. '.'. $oDtlColumn;
            }

            foreach($this->deliHdrColumn as $dHdrColumn) {
                $selectColumn[] = $this->deliHdrTableName. '.'. $dHdrColumn;
            }

            $orderDtlDb->select($selectColumn);

            $orderDtlDb->where($this->orderDtlTableName. '.t_order_hdr_id', '=', $oDest['t_order_hdr_id']);
            $orderDtlDb->where($this->orderDtlTableName. '.t_order_destination_id', '=', $oDest['t_order_destination_id']);

            $orderDtlDb->orderBy('order_dtl_seq');

            $orderDtlRows = $this->getArrayDbSelect(array_merge($this->orderDtlColumn, $this->deliHdrColumn), $orderDtlDb);

            $oDestRow['order_dtl'] = $orderDtlRows;

            $orderDestinations[] = $oDestRow;
        }

        $iRow['order_destination'] = $orderDestinations;

        // 受注タグ情報の取得
        $orderTagDb = DB::table($localDbName. '.'. $this->orderTagTableName);

        $orderTagDb->where('t_order_hdr_id', '=', $iRow['t_order_hdr_id']);
        $orderTagDb->leftJoin($localDbName. '.'. $this->masterOrderTagTableName, $this->orderTagTableName. '.m_order_tag_id', '=', $this->masterOrderTagTableName. '.m_order_tag_id');
        $orderTagDb->select($this->orderTagTableName. '.*');
        $orderTagDb->orderBy($this->masterOrderTagTableName.'.m_order_tag_sort');
        $orderTagDb->orderBy($this->orderTagTableName. '.m_order_tag_id');

        $orderTags = $this->getArrayDbSelect($this->orderTagColumn, $orderTagDb);

        $iRow['order_tag'] = $orderTags;

        return $iRow;
    }
}
