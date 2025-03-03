<?php


namespace App\Models\Claim\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BankPaymentCreditInModel
 * 
 * @package App\Models
 */
class BankPaymentCreditInModel extends Model
{
    protected $table = 'w_bank_payment_credit_in';
    protected $primaryKey = 'w_bank_payment_credit_id';

    public $timestamps = false;

    protected $fillable = [
        'in_shop_id',
        'in_order_id',
        'in_deal_status',
        'in_item_code',
        'in_amount',
        'in_tax',
        'in_payment_type',
        'in_payment_count',
        'in_card_type',
        'in_user_id',
        'in_spare_field_1',
        'in_spare_field_2',
        'in_merchant_custom_field_1',
        'in_merchant_custom_field_2',
        'in_merchant_custom_field_3',
        'in_deal_id',
        'in_deal_password',
        'in_transaction_id',
        'in_approval_no',
        'in_forwarding_code',
        'in_error_code',
        'in_error_detail_code',
        'in_process_date',
    ];

    protected $hidden = [
        'in_deal_password'
    ];
}
