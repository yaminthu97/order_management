<?php


namespace App\Models\Claim\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BankPaymentSmbcInModel
 * 
 * @package App\Models
 */
class BankPaymentSmbcInModel extends Model
{
    protected $table = 'w_bank_payment_smbc_in';
    protected $primaryKey = 'w_bank_payment_smbc_id';

    public $timestamps = false;
}
