<?php


namespace App\Models\Order\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderUpdateOutModel
 * 
 * @package App\Models
 */
class OrderUpdateOutModel extends Model
{
    protected $table = 'w_order_update_out';
    protected $primaryKey = 't_order_update_out_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
