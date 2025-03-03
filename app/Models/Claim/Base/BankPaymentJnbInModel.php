<?php


namespace App\Models\Claim\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BankPaymentJnbInModel
 * 
 * @package App\Models
 */
class BankPaymentJnbInModel extends Model
{
    protected $table = 'w_bank_payment_jnb_in';
    protected $primaryKey = 'w_bank_payment_jnb_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
