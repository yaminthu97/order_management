<?php


namespace App\Models\Claim\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BankPaymentCollectInModel
 * 
 * @package App\Models
 */
class BankPaymentCollectInModel extends Model
{
    protected $table = 'w_bank_payment_collect_in';
    protected $primaryKey = 'w_bank_payment_collect_id';

    public $timestamps = false;

    protected $fillable = [
        't_execute_batch_instruction_id',
        'file_created_date',
        'header_record',
        'payment_type',
        'customer_code',
        'shipping_label',
        'data_type',
        'correction_type',
        'shipping_date',
        'sell_amount',
        'collect_fee',
        'revenue_stamp_fee',
        'return_date',
        'return_shipping_label',
        'trailer_record',
        'end_record',
        'collected_date',
        'order_hdr_id',
        'cust_id',
        'amount',
        'account_payment_date',
    ];   
}
