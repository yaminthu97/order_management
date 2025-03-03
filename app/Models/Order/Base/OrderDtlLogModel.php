<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderDtlLogModel
 * 
 * @package App\Models
 */
class OrderDtlLogModel extends Model
{
    protected $table = 't_order_dtl_log';
    protected $primaryKey = 't_order_dtl_log_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
