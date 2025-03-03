<?php

namespace App\Models\Claim\Gfh1207;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BillingHdrModel
 *
 * @package App\Models
 */
class BillingHdrModel extends Model
{
    protected $table = 't_billing_hdr';
    protected $primaryKey = 't_billing_hdr_id';
    public $timestamps = false;

    protected $fillable = [
        'history_no',
        'is_available',
        'm_account_id',
        't_order_hdr_id',
        'invoiced_customer_id',
        'invoiced_customer_name_kanji',
        'postal',
        'address1',
        'address2',
        'address3',
        'address4',
        'corporate_kanji',
        'corporate_kana',
        'division_name',
        'corporate_tel',
        'note',
        'billing_amount',
        'tax_included_product_price',
        'tax_price',
        'standard_tax_price',
        'reduce_tax_price',
        'standard_tax_excluded_total_price',
        'reduce_tax_excluded_total_price',
        'discount_amount',
        'standard_discount',
        'reduce_discount',
        'tax_included_fee',
        'tax_included_shipping_fee',
        'detail_info',
        'm_payment_types_id',
        'finance_code',
        'cvs_company_code',
        'jp_account_num',
        'bank_code',
        'bank_shop_code',
        'bank_name',
        'bank_shop_name',
        'bank_account_type',
        'bank_account_num',
        'bank_account_name',
        'bank_account_name_kana',
        'output_count',
        'remind_count',
    ];

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 受注基本とのリレーション
     */
    public function order()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 請求先顧客とのリレーション
     */
    public function billCustomer()
    {
        return $this->belongsTo(\App\Models\Cc\Base\CustModel::class, 'invoiced_customer_id', 'm_cust_id');
    }

    /**
     * 支払方法マスタとのリレーション
     */
    public function paymentType()
    {
        return $this->belongsTo(\App\Models\Master\Base\PaymentTypeModel::class, 'm_payment_types_id', 'm_payment_types_id');
    }

    /**
     * 請求書出力履歴とのリレーション
     */
    public function billingOutputs()
    {
        return $this->hasMany(\App\Models\Claim\Gfh1207\BillingOutputModel::class, 't_billing_hdr_id', 't_billing_hdr_id');
    }

    /**
     * 受注基本とのリレーション
     */
    public function orderHdr()
    {
        return $this->hasOne(\App\Models\Order\Gfh1207\OrderHdrModel::class, 't_order_hdr_id', 't_billing_hdr_id');
    }

}
