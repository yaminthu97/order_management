<?php


namespace App\Models\Common\Itoki;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ItokiOrderHistoryModel
 *
 * @package App\Models
 */
class ItokiOrderHistoryModel extends Model
{
    protected $table = 't_itoki_order_history';
    protected $primaryKey = 't_itoki_order_history_id';
    protected $connection = 'global';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
