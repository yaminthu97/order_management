<?php


namespace App\Models\Claim\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CreditResultInModel
 * 
 * @package App\Models
 */
class CreditResultInModel extends Model
{
    protected $table = 'w_credit_result_in';
    protected $primaryKey = 't_credit_result_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
