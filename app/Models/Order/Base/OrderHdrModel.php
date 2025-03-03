<?php


namespace App\Models\Order\Base;

use App\Enums\DeliDecisionTypeEnum;
use App\Enums\DeliInstructTypeEnum;
use App\Enums\ProgressTypeEnum;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use App\Enums\AddressCheckTypeEnum;
// use App\Enums\AlertCustCheckTypeEnum;
// use App\Enums\CommentCheckTypeEnum;
// use App\Enums\CreditTypeEnum;
// use App\Enums\PaymentTypeEnum;
// use App\Enums\ReservationTypeEnum;
// use App\Enums\SalesStatusTypeEnum;
// use App\Enums\SettlementSalesTypeEnum;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;


/**
 * Class OrderHdrModel
 * 
 * @package App\Models
 */
class OrderHdrModel extends Model
{
    use HasFactory;
    protected $table = 't_order_hdr';
    protected $primaryKey = 't_order_hdr_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    
    protected $fillable = [
		't_order_hdr_id',
		'm_account_id',
		'ec_order_num',
		'm_cust_id',
		'order_operator_id',
		'order_type',
        'sales_store',
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
        'm_cust_id_billing',
        'billing_tel1',
        'billing_tel2',
        'billing_fax',
        'billing_email1',
        'billing_email2',
        'billing_postal',
        'billing_address1',
        'billing_address2',
        'billing_address3',
        'billing_address4',
        'billing_corporate_name',
        'billing_division_name',
        'billing_name',
        'billing_name_kana',
        'billing_destination_id',
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
        'estimate_flg',
		'tax_price',
		'order_dtl_coupon_id',
		'order_comment',
		'gift_flg',
        'campaign_flg',
        'pending_flg',
		'immediately_deli_flg',
		'rakuten_super_deal_flg',
		'mall_member_id',
		'multiple_deli_flg',
		'reservation_skip_flg',
		'progress_type',
        'receipt_type',
		'credit_type',
		'credit_datetime',
		'payment_type',
		'payment_datetime',
		'payment_transaction_id',
		'cb_billed_type',
		'cb_credit_status',
		'receipt_direction',
		'receipt_proviso',
		'order_datetime',
		'cancel_operator_id',
		'cancel_timestamp',
		'cancel_type',
		'cancel_note'
    ];

    /*
     * 企業アカウントとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    public function orderOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'order_operator_id', 'order_operator_id');
    }

    /*
     * ECサイトマスタとのリレーション
     */
    public function ecs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'm_ecs_id', 'm_ecs_id');
    }

    /**
     * 顧客とのリレーション（注文主）
     */
    public function cust()
    {
        return $this->belongsTo(\App\Models\Cc\Base\CustModel::class, 'm_cust_id', 'm_cust_id');
    }

    /**
     * 顧客とのリレーション（請求先）
     */
    public function billingCust()
    {
        return $this->belongsTo(\App\Models\Cc\Base\CustModel::class, 'm_cust_id_billing', 'm_cust_id');
    }

    /**
     * 送付マスタとのリレーション（請求書同梱送付先）
     */
    public function billingDestination()
    {
        return $this->belongsTo(\App\Models\Order\Base\DestinationModel::class, 'billing_destination_id', 'm_destination_id');
    }

    /**
     * 送付先マスタとのリレーション
     */
    public function orderDestination()
    {
        return $this->hasMany(\App\Models\Order\Base\OrderDestinationModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 受注明細とのリレーション
     */
    public function orderDtl()
    {
        return $this->hasMany(\App\Models\Order\Base\OrderDtlModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 受注明細SKUとのリレーション
     */
    public function orderDtlSku()
    {
        return $this->hasMany(\App\Models\Order\Base\OrderDtlSkuModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 受注タグとのリレーション
     */
    public function orderTags()
    {
        return $this->hasMany(\App\Models\Order\Base\OrderTagModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 配送基本とのリレーション
     */
    public function deliHdr()
    {
        return $this->hasMany(\App\Models\Order\Base\DeliHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 支払方法マスタとのリレーション
     */
    public function paymentTypes()
    {
        return $this->belongsTo(\App\Models\Master\Base\PaymentTypeModel::class, 'm_payment_types_id', 'm_payment_types_id');
    }

    public function progressUpdateOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'progress_update_operator_id', 'm_operators_id');
    }

    /**
     * 注文メモとのリレーション
     */
    public function orderMemo()
    {
        return $this->hasOne(\App\Models\Order\Base\OrderMemoModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 同梱先受注
     */
    public function bundleOrder()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderHdrModel::class, 'bundle_order_id', 't_order_hdr_id');
    }

    /**
     * 登録ユーザ
     */
    public function entryOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'entry_operator_id', 'm_operators_id');
    }

    /**
     * 更新ユーザ
     */
    public function updateOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'update_operator_id', 'm_operators_id');
    }

    /**
     * 取消ユーザ
     */
    public function cancelOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'cancel_operator_id', 'm_operators_id');
    }

    /**
     * 販売窓口
     */
    public function salesStoreItemnameTypes()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'sales_store', 'm_itemname_types_id');
    }

    /**
     * 進捗区分の表示文字列
     */
    public function displayProgressType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return ProgressTypeEnum::tryFrom($attribute['progress_type'])?->label();
        });
    }

    /**
     * 出荷指示区分の表示文字列
     */
    public function displayDeliInstructType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return DeliInstructTypeEnum::tryFrom($attribute['deli_instruct_type'])?->label();
        });
    }

    /**
     * 出荷確定区分の表示文字列
     */
    public function displayDeliDecisionType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return DeliDecisionTypeEnum::tryFrom($attribute['deli_decision_type'])?->label();
        });
    }

    /**
     * 受注方法（項目名称マスタ）
     */
    public function orderType()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'order_type', 'm_itemname_types_id');
    }
}
