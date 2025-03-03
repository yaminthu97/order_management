<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderDtlSkuSaveModel
 * 
 * @package App\Models
 */
class OrderDtlSkuSaveModel extends Model
{
    protected $table = 't_order_dtl_sku_save';
    protected $primaryKey = 't_order_dtl_sku_id';
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
