<?php


namespace App\Models\Claim\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BankPaymentCandidateModel
 * 
 * @package App\Models
 */
class BankPaymentCandidateModel extends Model
{
    protected $table = 'w_bank_payment_candidate';
    protected $primaryKey = 'w_bank_payment_candidate_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
