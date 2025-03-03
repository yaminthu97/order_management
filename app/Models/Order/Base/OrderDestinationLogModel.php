<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderDestinationLogModel
 * 
 * @package App\Models
 */
class OrderDestinationLogModel extends Model
{
    protected $table = 't_order_destination_log';
    protected $primaryKey = 't_order_destination_log_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
