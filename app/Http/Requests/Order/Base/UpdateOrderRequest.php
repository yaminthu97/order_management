<?php

namespace App\Http\Requests\Order\Base;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Validator\CustomValidator;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // キャンセル場合はバリデーションをスキップ
        if ($this->has('submit_cancel')) {
            return [];
        }
        return [
            // 受注情報
            't_order_hdr_id'			=>	[ 'nullable',	'required_with:cancel_timestamp',	'numeric' ],
            'ec_order_num'				=>	[ 'nullable',	'str_length_max_count:100' ],
            'order_operator_id'			=>	[ 'nullable',	'numeric' ],
            'order_type'				=>	[ 'nullable' ],
            'm_ecs_id'					=>	[ 'required' ,	'numeric' ],
            'sales_counter_id'			=>	[ 'nullable',	'numeric' ],
            'estimate_flg'				=>	[ 'nullable',	'in:0,1' ],
            'campaign_flg'				=>	[ 'nullable',	'in:0,1' ],

            // 注文主情報
            'm_cust_id'					=>	[ 'required',	'required_with:t_order_hdr_id',	'numeric' ],
            'order_tel1'				=>	[ 'required',	'str_length_max_count:20', ],
            'order_tel2'				=>	[ 'nullable',	'str_length_max_count:20', ],
            'order_fax'					=>	[ 'nullable',	'str_length_max_count:20', ],
            'order_email1'				=>	[ 'nullable',	'str_length_max_count:255',	'email_notrfc' ],
            'order_email2'				=>	[ 'nullable',	'str_length_max_count:255',	'email_notrfc' ],
            'order_postal'				=>	[ 'required',	'str_length_max_count:7'],
            'order_address1'			=>	[ 'required',	'str_length_max_count:100' ],
            'order_address2'			=>	[ 'required',	'str_length_max_count:100' ],
            'order_address3'			=>	[ 'nullable',	'str_length_max_count:100' ],
            'order_address4'			=>	[ 'nullable',	'str_length_max_count:100' ],
            'order_corporate_name'		=>	[ 'nullable',	'str_length_max_count:100' ],
            'order_division_name'		=>	[ 'nullable',	'str_length_max_count:100' ],
            'order_name'				=>	[ 'required',	'str_length_max_count:100' ],
            'order_name_kana'			=>	[ 'nullable',	'str_length_max_count:100' ],

            // 請求先情報
            'm_cust_id_billing'			=>	[ 'nullable' ],
            'billing_tel1'				=>	[ 'nullable',	'str_length_max_count:20', ],
            'billing_tel2'				=>	[ 'nullable',	'str_length_max_count:20', ],
            'billing_fax'				=>	[ 'nullable',	'str_length_max_count:20', ],
            'billing_email1'			=>	[ 'nullable',	'str_length_max_count:255',	'email_notrfc' ],
            'billing_email2'			=>	[ 'nullable',	'str_length_max_count:255',	'email_notrfc' ],
            'billing_postal'			=>	[ 'required',	'str_length_max_count:7'],
            'billing_address1'			=>	[ 'required',	'str_length_max_count:100' ],
            'billing_address2'			=>	[ 'required',	'str_length_max_count:100' ],
            'billing_address3'			=>	[ 'nullable',	'str_length_max_count:100' ],
            'billing_address4'			=>	[ 'nullable',	'str_length_max_count:100' ],
            'billing_corporate_name'		=>	[ 'nullable',	'str_length_max_count:100' ],
            'billing_division_name'		=>	[ 'nullable',	'str_length_max_count:100' ],
            'billing_name'				=>	[ 'required',	'str_length_max_count:100' ],
            'billing_name_kana'			=>	[ 'nullable',	'str_length_max_count:100' ],

            // 決済情報
            'pay_type_name'				=>	[ 'required' ],
            'card_company'				=>	[ 'nullable',	'str_length_max_count:100' ],
            'card_holder'				=>	[ 'nullable',	'str_length_max_count:100' ],
            'card_pay_times'			=>	[ 'nullable',	'numeric' ],
            'alert_order_flg'			=>	[ 'nullable',	'in:1' ],
            'tax_rate'					=>	[ 'nullable',	'numeric',	'between:0,1' ],
            'sell_total_price'			=>	[ 'required',	'numeric' ],
            'discount'					=>	[ 'required',	'numeric' ],
            'shipping_fee'				=>	[ 'required',	'numeric' ],
            'payment_fee'				=>	[ 'required',	'numeric' ],
            'package_fee'				=>	[ 'required',	'numeric' ],
            'use_point'					=>	[ 'required',	'numeric' ],
            'use_coupon_store'			=>	[ 'required',	'numeric' ],
            'use_coupon_mall'			=>	[ 'required',	'numeric' ],
            'total_use_coupon'			=>	[ 'required',	'numeric' ],
            'order_total_price'			=>	[ 'required',	'numeric' ],
            'tax_price'					=>	[ 'nullable',	'numeric' ],
            'standard_total_price'      =>  [ 'nullable',	'numeric' ],
            'reduce_total_price'        =>  [ 'nullable',	'numeric' ],
            'standard_tax_price'        =>  [ 'nullable',	'numeric' ],
            'reduce_tax_price'          =>  [ 'nullable',	'numeric' ],
            'order_dtl_coupon_id'		=>	[ 'nullable',	'str_length_max_count:1000' ],
            'order_comment'				=>	[ 'nullable' ],
            'operator_comment'			=>	[ 'nullable' ],
            'billing_comment'			=>	[ 'nullable' ],
            'gift_flg'					=>	[ 'nullable',	'in:0,1' ],
            'immediately_deli_flg'		=>	[ 'nullable',	'in:1' ],
            'rakuten_super_deal_flg'	=>	[ 'nullable',	'in:1' ],
            'mall_member_id'			=>	[ 'nullable',	'str_length_max_count:255' ],
            'reservation_skip_flg'		=>	[ 'nullable',	'in:1' ],
            'credit_type'				=>	[ 'nullable',	'in:0,1,2,9' ],
            'payment_type'				=>	[ 'nullable',	'in:0,1,2,9' ],
            'payment_transaction_id'	=>	[ 'nullable',	'str_length_max_count:100' ],
            'cb_billed_type'			=>	[ 'nullable',	'in:0,1' ],
            'receipt_direction'			=>	[ 'nullable',	'str_length_max_count:120' ],
            'receipt_proviso'			=>	[ 'nullable',	'str_length_max_count:30' ],

            // 受注情報2
            'order_datetime'			=>	[ 'required',	'date' ],
            'operator_id'				=>	[ 'nullable',	'numeric' ],
            'cancel_timestamp'			=>	[ 'nullable',	'date' ],
            'cancel_type'				=>	[ 'nullable',	'required_with:cancel_timestamp',	'numeric' ],
            'cancel_note'				=>	[ 'nullable' ],

            // 配送先情報
            'register_destination'		=>	[ 'required',	'array' ],
            'register_destination.*.t_order_destination_id'		=>	[ 'nullable',	'numeric' ],
            'register_destination.*.order_destination_seq'		=>	[ 'required',	'numeric' ],
            'register_destination.*.destination_alter_flg'		=>	[ 'nullable',	'in:1' ],
            'register_destination.*.destination_tel'			=>	[ 'nullable',	'str_length_max_count:20', ],
            'register_destination.*.destination_postal'			=>	[ 'nullable',	'str_length_max_count:7'],
            'register_destination.*.destination_address1'		=>	[ 'required',	'str_length_max_count:100' ],
            'register_destination.*.destination_address2'		=>	[ 'required',	'str_length_max_count:100' ],
            'register_destination.*.destination_address3'		=>	[ 'nullable',	'str_length_max_count:100' ],
            'register_destination.*.destination_address4'		=>	[ 'nullable',	'str_length_max_count:100' ],
            'register_destination.*.destination_company_name'	=>	[ 'nullable',	'str_length_max_count:100' ],
            'register_destination.*.destination_division_name'	=>	[ 'nullable',	'str_length_max_count:100' ],
            'register_destination.*.destination_name_kana'		=>	[ 'nullable',	'str_length_max_count:100' ],
            'register_destination.*.destination_name'			=>	[ 'required',	'str_length_max_count:100' ],
            'register_destination.*.deli_hope_date'				=>	[ 'nullable',	'date' ],
            'register_destination.*.deli_hope_time_name'		=>	[ 'nullable',	'str_length_max_count:100' ],
            'register_destination.*.delivery_name'				=>	[ 'required' ],
            'register_destination.*.shipping_fee'				=>	[ 'required',	'numeric' ],
            'register_destination.*.payment_fee'				=>	[ 'required',	'numeric' ],
            'register_destination.*.wrapping_fee'				=>	[ 'required',	'numeric' ],
            'register_destination.*.deli_plan_date'				=>	[ 'nullable',	'date' ],
            'register_destination.*.gift_message'				=>	[ 'nullable' ],
            'register_destination.*.gift_wrapping'				=>	[ 'nullable' ],
            'register_destination.*.nosi_type'					=>	[ 'nullable' ],
            'register_destination.*.nosi_name'					=>	[ 'nullable' ],
            'register_destination.*.invoice_comment'			=>	[ 'nullable' ],
            'register_destination.*.picking_comment'			=>	[ 'nullable' ],
            'register_destination.*.partial_deli_flg'			=>	[ 'nullable',	'in:0,1' ],
            'register_destination.*.ec_destination_num'			=>	[ 'nullable',	'str_length_max_count:100' ],
            'register_destination.*.pending_flg'				=>	[ 'nullable',	'in:0,1' ],
            'sender_name'										=>	[ 'nullable',	'str_length_max_count:100' ],
            'total_deli_flg'									=>	[ 'nullable',	'in:0,1' ],
            'total_temperature_zone_type'						=>	[ 'nullable',	'in:0,1,2' ],

            // 受注明細情報
            'register_destination.*.register_detail'			=>	[ 'required',	'array' ],
            'register_destination.*.register_detail.*.t_order_dtl_id'			=>	[ 'nullable',	'required_with:register_destination.*.register_detail.*.cancel_timestamp',	'numeric' ],
            'register_destination.*.register_detail.*.order_dtl_seq'			=>	[ 'required',	'numeric' ],
            'register_destination.*.register_detail.*.sell_cd'					=>	[ 'required',	'str_length_max_count:100' ],
            'register_destination.*.register_detail.*.sell_option'				=>	[ 'nullable',	'str_length_max_count:1000' ],
            'register_destination.*.register_detail.*.sell_name'				=>	[ 'required' ],
            'register_destination.*.register_detail.*.order_dtl_coupon_id'		=>	[ 'nullable',	'str_length_max_count:1000' ],
            'register_destination.*.register_detail.*.order_dtl_coupon_price'	=>	[ 'nullable',	'numeric' ],
            'register_destination.*.register_detail.*.order_sell_price'			=>	[ 'required',	'numeric' ],
            'register_destination.*.register_detail.*.order_sell_vol'			=>	[ 'required',	'numeric',	'min:1' ],
            'register_destination.*.register_detail.*.tax_rate'					=>	[ 'required',	'numeric',	'between:0,1' ],
            'register_destination.*.register_detail.*.tax_price'				=>	[ 'nullable',	'numeric' ],
            'register_destination.*.register_detail.*.cancel_timestamp'			=>	[ 'nullable',	'required_with:cancel_timestamp',	'date' ],

            // 熨斗情報
            'register_destination.*.register_detail.*.t_order_dtl_noshi'			=>	[ 'required',	'array' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.count'						=>	[ 'required',	'numeric' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.noshi_detail_id'				=>	[ 'required',	'numeric' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.m_noshi_naming_pattern_id'	=>	[ 'required',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.attach_flg'					=>	[ 'required',	'in:0,1' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.company_name1'				=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.company_name2'				=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.company_name3'				=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.company_name4'				=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.company_name5'				=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.section_name1'				=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.section_name2'				=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.section_name3'				=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.section_name4'				=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.section_name5'				=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.title1'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.title2'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.title3'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.title4'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.title5'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.firstname1'					=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.firstname2'					=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.firstname3'					=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.firstname4'					=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.firstname5'					=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.name1'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.name2'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.name3'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.name4'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.name5'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.ruby1'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.ruby2'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.ruby3'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.ruby4'						=>	[ 'nullable',	'str_length_max_count:256' ],
            'register_destination.*.register_detail.*.t_order_dtl_noshi.ruby5'						=>	[ 'nullable',	'str_length_max_count:256' ],

            // 付属品情報
            'register_destination.*.register_detail.*.t_order_dtl_attachment_items'				=>	[ 'nullable',	'array' ],
            'register_destination.*.register_detail.*.t_order_dtl_attachment_items.*.attachment_item_id'		=>	[ 'required',	'numeric' ],
            'register_destination.*.register_detail.*.t_order_dtl_attachment_items.*.attachment_item_cd'		=>	[ 'required',	'numeric' ],
            'register_destination.*.register_detail.*.t_order_dtl_attachment_items.*.attachment_item_name'		=>	[ 'required',	'numeric' ],
            'register_destination.*.register_detail.*.t_order_dtl_attachment_items.*.attachment_vol'		=>	[ 'required',	'numeric' ],

            // 受注明細SKU情報
            'register_destination.*.register_detail.*.register_detail_sku'		=>	[ 'required',	'array' ],
            'register_destination.*.register_detail.*.register_detail_sku.*.t_order_dtl_sku_id'		=>	[ 'nullable',	'required_with:register_destination.*.register_detail.*.t_order_dtl_id',	'numeric' ],
            'register_destination.*.register_detail.*.register_detail_sku.*.item_cd'				=>	[ 'required',	'str_length_max_count:100' ],
            'register_destination.*.register_detail.*.register_detail_sku.*.item_vol'				=>	[ 'required',	'numeric',	'min:1' ]
        ];
    }

    /**
     * 項目名
     */
    public function attributes()
    {
        // langファイルとの重複項目確認
        return [
            't_order_hdr_id'			=>	'受注ID',
            'ec_order_num'				=>	'ECサイト注文ID',
            'm_cust_id'					=>	'顧客ID',
            'order_operator_id'			=>	'受注担当者',
            'order_type'				=>	'注文方法',
            'm_ecs_id'					=>	'ECサイトID',
            'sales_counter_id'			=>	'販売窓口',
            'estimate_flg'				=>	'見積',
            'receipt_type'                =>	'領収書',
            'campaign_flg'				=>	'キャンペーン',

            'order_tel1'				=>	'注文主電話番号１',
            'order_tel2'				=>	'注文主電話番号２',
            'order_fax'					=>	'注文主ＦＡＸ番号',
            'order_email1'				=>	'注文主メールアドレス１',
            'order_email2'				=>	'注文主メールアドレス２',
            'order_postal'				=>	'注文主郵便番号',
            'order_address1'			=>	'注文主都道府県',
            'order_address2'			=>	'注文主市区町村',
            'order_address3'			=>	'注文主番地',
            'order_address4'			=>	'注文主建物名',
            'order_corporate_name'		=>	'注文主法人名・団体名',
            'order_division_name'		=>	'注文主部署名',
            'order_name'				=>	'注文主氏名',
            'order_name_kana'			=>	'注文主氏名カナ',

            'm_cust_id_billing'			=>	'請求先顧客ID',
            'billing_tel1'				=>	'請求先電話番号１',
            'billing_tel2'				=>	'請求先電話番号２',
            'billing_fax'				=>	'請求先ＦＡＸ番号',
            'billing_email1'			=>	'請求先メールアドレス１',
            'billing_email2'			=>	'請求先メールアドレス２',
            'billing_postal'			=>	'請求先郵便番号',
            'billing_address1'			=>	'請求先都道府県',
            'billing_address2'			=>	'請求先市区町村',
            'billing_address3'			=>	'請求先番地',
            'billing_address4'			=>	'請求先建物名',
            'billing_corporate_name'	=>	'請求先法人名・団体名',
            'billing_division_name'		=>	'請求先部署名',
            'billing_name'				=>	'請求先氏名',
            'billing_name_kana'			=>	'請求先氏名カナ',

            'pay_type_name'				=>	'支払い方法名',
            'card_company'				=>	'カード会社',
            'card_holder'				=>	'カード名義人',
            'card_pay_times'			=>	'カード分割回数',
            'alert_order_flg'			=>	'要注意注文フラグ',
            'tax_rate'					=>	'消費税率',
            'sell_total_price'			=>	'商品購入金額',
            'discount'					=>	'割引額',
            'shipping_fee'				=>	'送料合計',
            'payment_fee'				=>	'手数料合計',
            'package_fee'				=>	'包装料合計',
            'use_point'					=>	'ポイント利用額',
            'use_coupon_store'			=>	'ストアクーポン利用額',
            'use_coupon_mall'			=>	'モールクーポン利用額',
            'total_use_coupon'			=>	'クーポン利用額計',
            'order_total_price'			=>	'請求金額',
            'tax_price'					=>	'消費税額',
            'order_dtl_coupon_id'		=>	'クーポンID',
            'order_comment'				=>	'備考',
            'operator_comment'			=>	'社内メモ',
            'billing_comment'			=>	'請求メモ',
            'gift_flg'					=>	'ギフトフラグ',
            'immediately_deli_flg'		=>	'即日配送',
            'rakuten_super_deal_flg'	=>	'楽天スーパーDEAL',
            'mall_member_id'			=>	'モール会員ID',
            'reservation_skip_flg'		=>	'引当スキップフラグ',
            'credit_type'				=>	'与信区分',
            'payment_type'				=>	'入金区分',
            'payment_transaction_id'	=>	'決済代行取引ID',
            'cb_billed_type'			=>	'請求書送付種別',
            'receipt_direction'			=>	'領収証宛名',
            'receipt_proviso'			=>	'領収証但し書きコメント',
            'order_datetime'			=>	'受注日時',
            'operator_id'				=>	'ユーザID',
            'cancel_timestamp'			=>	'取消タイムスタンプ',
            'cancel_type'				=>	'取消理由',
            'cancel_note'				=>	'取消備考',

            'register_destination'		=>	'登録配送先',
            'register_destination.*.t_order_destination_id'		=>	'受注配送先ID',
            'register_destination.*.order_destination_seq'		=>	'配送先番号',
            'register_destination.*.destination_alter_flg'		=>	'配送先変更フラグ',
            'register_destination.*.destination_tel'			=>	'配送先電話番号',
            'register_destination.*.destination_postal'			=>	'配送先郵便番号',
            'register_destination.*.destination_address1'		=>	'配送先都道府県',
            'register_destination.*.destination_address2'		=>	'配送先市区町村',
            'register_destination.*.destination_address3'		=>	'配送先番地',
            'register_destination.*.destination_address4'		=>	'配送先建物名',
            'register_destination.*.destination_company_name'	=>	'配送先法人名・団体名',
            'register_destination.*.destination_division_name'	=>	'配送先部署名',
            'register_destination.*.destination_name_kana'		=>	'配送先氏名カナ',
            'register_destination.*.destination_name'			=>	'配送先氏名',
            'register_destination.*.deli_hope_date'				=>	'配送希望日',
            'register_destination.*.deli_hope_time_name'		=>	'配送時間帯文字列',
            'register_destination.*.delivery_name'				=>	'配送方法文字列',
            'register_destination.*.shipping_fee'				=>	'送料',
            'register_destination.*.payment_fee'				=>	'手数料',
            'register_destination.*.wrapping_fee'				=>	'包装料',
            'register_destination.*.deli_plan_date'				=>	'出荷予定日',
            'register_destination.*.gift_message'				=>	'ギフトメッセージ',
            'register_destination.*.gift_wrapping'				=>	'ギフト包装種類',
            'register_destination.*.nosi_type'					=>	'のしタイプ',
            'register_destination.*.nosi_name'					=>	'のし名前',
            'register_destination.*.invoice_comment'			=>	'送り状コメント',
            'register_destination.*.picking_comment'			=>	'ピッキングコメント',
            'register_destination.*.partial_deli_flg'			=>	'分割出荷フラグ',
            'register_destination.*.ec_destination_num'			=>	'ECサイト送付先ID',
            'register_destination.*.pending_flg'				=>	'出荷保留フラグ',
            'sender_name'										=>	'送り主名',
            'total_deli_flg'									=>	'同梱配送',
            'total_temperature_zone_type'						=>	'温度帯',

            'register_destination.*.register_detail'			=>	'登録明細',
            'register_destination.*.register_detail.*.t_order_dtl_id'			=>	'受注明細ID',
            'register_destination.*.register_detail.*.order_dtl_seq'			=>	'明細番号',
            'register_destination.*.register_detail.*.sell_cd'					=>	'販売コード',
            'register_destination.*.register_detail.*.sell_option'				=>	'項目選択肢',
            'register_destination.*.register_detail.*.sell_name'				=>	'販売商品名',
            'register_destination.*.register_detail.*.order_dtl_coupon_id'		=>	'明細クーポンID',
            'register_destination.*.register_detail.*.order_dtl_coupon_price'	=>	'明細クーポン金額',
            'register_destination.*.register_detail.*.order_sell_price'			=>	'販売単価',
            'register_destination.*.register_detail.*.order_sell_vol'			=>	'受注数量',
            'register_destination.*.register_detail.*.tax_rate'					=>	'明細消費税率',
            'register_destination.*.register_detail.*.tax_price'				=>	'明細消費税額',
            'register_destination.*.register_detail.*.cancel_timestamp'			=>	'明細取消タイムスタンプ',

            
            'register_destination.*.register_detail.*.t_order_dtl_noshi'			=>	'熨斗',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.count'						=>	'熨斗数',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.noshi_detail_id'				=>	'熨斗ID',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.m_noshi_naming_pattern_id'	=>	'熨斗名前パターンID',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.attach_flg'					=>	'熨斗添付フラグ',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.company_name1'				=>	'熨斗会社名1',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.company_name2'				=>	'熨斗会社名2',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.company_name3'				=>	'熨斗会社名3',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.company_name4'				=>	'熨斗会社名4',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.company_name5'				=>	'熨斗会社名5',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.section_name1'				=>	'熨斗部署名1',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.section_name2'				=>	'熨斗部署名2',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.section_name3'				=>	'熨斗部署名3',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.section_name4'				=>	'熨斗部署名4',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.section_name5'				=>	'熨斗部署名5',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.title1'						=>	'熨斗肩書1(役職名)',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.title2'						=>	'熨斗肩書2(役職名)',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.title3'						=>	'熨斗肩書3(役職名)',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.title4'						=>	'熨斗肩書4(役職名)',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.title5'						=>	'熨斗肩書5(役職名)',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.firstname1'					=>	'熨斗苗字1',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.firstname2'					=>	'熨斗苗字2',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.firstname3'					=>	'熨斗苗字3',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.firstname4'					=>	'熨斗苗字4',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.firstname5'					=>	'熨斗苗字5',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.name1'						=>	'熨斗名前1',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.name2'						=>	'熨斗名前2',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.name3'						=>	'熨斗名前3',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.name4'						=>	'熨斗名前4',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.name5'						=>	'熨斗名前5',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.ruby1'						=>	'熨斗ルビ1',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.ruby2'						=>	'熨斗ルビ2',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.ruby3'						=>	'熨斗ルビ3',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.ruby4'						=>	'熨斗ルビ4',
            'register_destination.*.register_detail.*.t_order_dtl_noshi.ruby5'						=>	'熨斗ルビ5',

            'register_destination.*.register_detail.*.t_order_dtl_attachment_items'				=>	'付属品',
            'register_destination.*.register_detail.*.t_order_dtl_attachment_items.*.attachment_item_id'		=>	'付属品ID',
            'register_destination.*.register_detail.*.t_order_dtl_attachment_items.*.attachment_item_cd'		=>	'付属品コード',
            'register_destination.*.register_detail.*.t_order_dtl_attachment_items.*.attachment_item_name'		=>	'付属品名',
            'register_destination.*.register_detail.*.t_order_dtl_attachment_items.*.attachment_vol'		=>	'付属品数量',


            'register_destination.*.register_detail.*.register_detail_sku'		=>	'登録明細SKU',
            'register_destination.*.register_detail.*.register_detail_sku.*.t_order_dtl_sku_id'		=>	'受注明細SKUID',
            'register_destination.*.register_detail.*.register_detail_sku.*.item_cd'				=>	'商品コード',
            'register_destination.*.register_detail.*.register_detail_sku.*.item_vol'				=>	'商品数量'
        ];
    }

    /**
     * バリデーションメッセージ
     * デフォルトから変更したい場合はここに追加する
     */
    public function messages()
    {
        return [
            't_order_hdr_id.exists'					=>	'受注IDが存在しない、または、削除済です。',
            'order_fax.tel'							=>	'正しいＦＡＸ番号を入力してください。',
            'order_postal.str_length_max_count'		=>	'注文主郵便番号の桁数が不正です。',
            'order_postal.postal7only'				=>	'正しい注文主郵便番号を入力してください。',
            'register_destination.*.destination_postal.str_length_max_count'	=>	'配送先郵便番号の桁数が不正です。',
            'register_destination.*.destination_postal.postal7only'				=>	'正しい配送先郵便番号を入力してください。',
            'order_total_price.min'					=> '請求金額は:min円未満にできません。',
            'order_total_price.max'					=> '返品のため、請求金額は:max円より大きくできません。',
        ];
    }

    /**
     * バリデーション前処理
     */
    public function prepareForValidation()
    {
        //
    }
    

	/**
	 * 追加バリデーションルールの設定
	 *
	 * @param $requestData array
	 * @param $tableName string
	 * @param $connectionName string
	 */

     /*
	protected function addCustomRules($requestData, $tableName, $connectionName)
	{
		if (isset($requestData['t_order_hdr_id']))
		{
			$pKey = $requestData['t_order_hdr_id'];
		}
		else
		{
			$pKey = '';
		}

		//受注基本存在チェック
		$this->rules['t_order_hdr_id'][] = $this->createExistsRule('local.t_order_hdr', 't_order_hdr_id', 'cancel_timestamp', 0);
		//受注配送先存在チェック
		$this->rules['register_destination.*.t_order_destination_id'][] = $this->createExistsRule('local.t_order_destination', 't_order_destination_id', 't_order_hdr_id', $pKey);
		//受注明細存在チェック
		$this->rules['register_destination.*.register_detail.*.t_order_dtl_id'][] = $this->createExistsRule('local.t_order_dtl', 't_order_dtl_id', 't_order_hdr_id', $pKey);
		//受注明細SKU存在チェック
		$this->rules['register_destination.*.register_detail.*.register_detail_sku.*.t_order_dtl_sku_id'][] = $this->createExistsRule('local.t_order_dtl_sku', 't_order_dtl_sku_id', 't_order_hdr_id', $pKey);
		//EC注文ID重複チェック
		if (isset($requestData['m_ecs_id']) && strlen($requestData['m_ecs_id']) > 0)
		{
			$this->rules['ec_order_num'][] = Rule::unique('local.t_order_hdr')
				->ignore($pKey, 't_order_hdr_id')
				->where(function($query) use ($requestData) {
					$query->where('m_ecs_id', $requestData['m_ecs_id']);
//					$query->where('cancel_timestamp', 0);
				});
		}
        
        // 画面からの受注のみチェックを行う
        if(!empty($requestData['app_register_flg']))
        {
			// 請求金額
			if(isset($requestData['return_flg']) && $requestData['return_flg'] == 1){
				$this->rules['order_total_price'][] = 'max:0';
			}else{
				$this->rules['order_total_price'][] = 'min:0';
			}

			// 注文主電話番号
			$this->rules['order_tel1'] = ['required_without_all:order_tel2', 'str_length_max_count:20',];
			$this->rules['order_tel2'] = ['required_without_all:order_tel1', 'str_length_max_count:20',];
			// 注文主郵便番号
			$this->rules['order_postal'] = [ 'required',	'str_length_max_count:7',	'postal7only' ];

			// 送付先電話番号
			$this->rules['register_destination.*.destination_tel'] = [ 'required',	'str_length_max_count:20', ];
			// 送付先郵便番号
			$this->rules['register_destination.*.destination_postal'] = [ 'required',	'str_length_max_count:7',	'postal7only' ];
        }

		//返品の場合、数値は-1以下
		if(isset($requestData['return_flg']) && $requestData['return_flg'] == 1){
			$this->rules['register_destination.*.register_detail.*.order_sell_vol']=[ 'required',	'numeric',	'max:-1' ];
			$this->rules['register_destination.*.register_detail.*.register_detail_sku.*.item_vol']=[ 'required',	'numeric',	'max:-1' ];
		}
	}
    */
}
