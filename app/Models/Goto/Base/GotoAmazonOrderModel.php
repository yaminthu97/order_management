<?php


namespace App\Models\Goto\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoAmazonOrderModel
 *
 * @package App\Models
 */
class GotoAmazonOrderModel extends Model
{
    protected $table = 'w_goto_amazon_order';
    protected $primaryKey = 'w_goto_amazon_order_id';
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
