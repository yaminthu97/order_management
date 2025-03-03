<?php


namespace App\Models\Cc\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CustLogModel
 * 
 * @package App\Models
 */
class CustLogModel extends Model
{
    protected $table = 't_cust_log';
    protected $primaryKey = 't_cust_log_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
