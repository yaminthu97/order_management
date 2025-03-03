<?php


namespace App\Models\Order\Gfh1207;

use App\Enums\AddressCheckTypeEnum;
use App\Enums\AlertCustCheckTypeEnum;
use App\Enums\CommentCheckTypeEnum;
use App\Enums\CreditTypeEnum;
use App\Enums\DeliDecisionTypeEnum;
use App\Enums\DeliHopeDateCheckTypeEnum;
use App\Enums\DeliInstructTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Enums\ProgressTypeEnum;
use App\Enums\ReservationTypeEnum;
use App\Enums\SalesStatusTypeEnum;
use App\Enums\SettlementSalesTypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'order_count',
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
        'delivery_type_fee',
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
        'standard_total_price',
        'reduce_total_price',
        'standard_tax_price',
        'reduce_tax_price',
        'transfer_fee',
        'ec_order_change_flg',
        'ec_order_change_datetime',
        'ec_order_sync_flg',
        'ec_order_sync_datetime',
		'cancel_operator_id',
		'cancel_timestamp',
		'cancel_type',
		'cancel_note',
        'entry_operator_id',
        'update_operator_id',
    ];

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';

    /**
     * The attributes that are mass assignable.
     */
    public function casts()
    {
        return [
            // 'comment_check_type' => CommentCheckTypeEnum::class,
        ];
    }

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
     * 送付マスタとのリレーション
     */
    public function orderDestination()
    {
        return $this->hasMany(\App\Models\Order\Gfh1207\OrderDestinationModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 受注明細とのリレーション
     */
    public function orderDtl()
    {
        return $this->hasMany(\App\Models\Order\Base\OrderDtlModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 受注明細とのリレーション
     */
    public function orderDtls()
    {
        return $this->hasMany(\App\Models\Order\Gfh1207\OrderDtlModel::class, 't_order_hdr_id', 't_order_hdr_id');
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
        return $this->hasMany(\App\Models\Order\Base\OrderTagModel::class, 't_order_hdr_id', 't_order_hdr_id')
            ->where(function ($query) {
                $query->whereNull('cancel_operator_id')
                    ->orWhere('cancel_operator_id', 0);
            });
    }

    /**
     * 配送基本とのリレーション
     */
    public function deliHdr()
    {
        return $this->hasMany(\App\Models\Order\Base\DeliHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 項目名称マスタ
     */
    public function orderType()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'order_type', 'm_itemname_types_id');
    }

    /**
     * 入金情報とのリレーション
     */
    public function payment()
    {
        return $this->hasMany(\App\Models\Order\Base\PaymentModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * メール送信履歴とのリレーション
     */
    public function mailSendHistories()
    {
        return $this->hasMany(\App\Models\Cc\Base\MailSendHistoryModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 受注メモとのリレーション
     */
    public function orderMemo()
    {
        return $this->hasOne(\App\Models\Order\Base\OrderMemoModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 送付マスタとのリレーション（請求書同梱送付先）
     */
    public function billingDestination()
    {
        return $this->belongsTo(\App\Models\Order\Base\DestinationModel::class, 'billing_destination_id', 'm_destination_id');
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
     * 同梱先受注
     */
    public function bundleOrder()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderHdrModel::class, 'bundle_order_id', 't_order_hdr_id');
    }

    /*
     * 請求基本とのリレーション
     */
    public function billingHdr()
    {
        return $this->belongsTo(\App\Models\Claim\Gfh1207\BillingHdrModel::class, 't_billing_hdr_id', 't_billing_hdr_id');
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
     * 編集可能かどうかの判定
     */
    public function canEdit(): bool
    {
        return in_array($this->progress_type, [
            ProgressTypeEnum::PendingConfirmation->value,
            ProgressTypeEnum::PendingCredit->value,
            ProgressTypeEnum::PendingPrepayment->value,
            ProgressTypeEnum::PendingAllocation->value,
            ProgressTypeEnum::PendingShipment->value,
        ]);
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
     * 要注意顧客区分の表示文字列
     */
    public function displayAlertCustCheckType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return AlertCustCheckTypeEnum::tryFrom($attribute['alert_cust_check_type'])?->label();
        });
    }

    /**
     * 住所確認区分の表示文字列
     */
    public function displayAddressCheckType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return AddressCheckTypeEnum::tryFrom($attribute['address_check_type'])?->label();
        });
    }

    /**
     * 送付希望日確認区分の表示文字列
     */
    public function displayDeliHopeDateCheckType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return DeliHopeDateCheckTypeEnum::tryFrom($attribute['deli_hope_date_check_type'])?->label();
        });
    }

    /**
     * コメント確認区分の表示文字列
     */
    public function displayCommentCheckType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return CommentCheckTypeEnum::tryFrom($attribute['comment_check_type'])?->label();
        });
    }

    /**
     * 与信区分の表示文字列
     */
    public function displayCreditType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return CreditTypeEnum::tryFrom($attribute['credit_type'])?->label();
        });
    }

    /**
     * 入金区分の表示文字列
     */
    public function displayPaymentType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return PaymentTypeEnum::tryFrom($attribute['payment_type'])?->label();
        });
    }

    /**
     * 在庫引当区分の表示文字列
     */
    public function displayReservationType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return ReservationTypeEnum::tryFrom($attribute['reservation_type'])?->label();
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
     * 売上ステータス反映区分の表示文字列
     */
    public function displaySalesStatusType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return SalesStatusTypeEnum::tryFrom($attribute['sales_status_type'])?->label();
        });
    }

    /**
     * 決済売上計上区分の表示文字列
     */
    public function displaySettlementSalesType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return SettlementSalesTypeEnum::tryFrom($attribute['settlement_sales_type'])?->label();
        });
    }

    /**
     * 表示用の請求金額
     */
    public function displayOrderTotalPrice(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return number_format($attribute['order_total_price']);
        });
    }

    public function cancelType()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'cancel_type', 'm_itemname_types_id');
    }
}
