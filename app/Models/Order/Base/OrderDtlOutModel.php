<?php


namespace App\Models\Order\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderDtlOutModel
 * 
 * @package App\Models
 */
class OrderDtlOutModel extends Model
{
    protected $table = 'w_order_dtl_out';
    protected $primaryKey = 't_order_dtl_out_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
