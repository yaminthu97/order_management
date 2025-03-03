<?php


namespace App\Models\Claim\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BankPaymentStdInModel
 * 
 * @package App\Models
 */
class BankPaymentStdInModel extends Model
{
    protected $table = 'w_bank_payment_std_in';
    protected $primaryKey = 'w_bank_payment_std_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
