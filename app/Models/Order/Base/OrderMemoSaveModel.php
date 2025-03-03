<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderMemoSaveModel
 * 
 * @package App\Models
 */
class OrderMemoSaveModel extends Model
{
    protected $table = 't_order_memo_save';
    protected $primaryKey = 't_order_memo_id';
    public $incrementing = false;

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
