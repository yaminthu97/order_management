<?php


namespace App\Models\Order\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderUpdateInModel
 * 
 * @package App\Models
 */
class OrderUpdateInModel extends Model
{
    protected $table = 'w_order_update_in';
    protected $primaryKey = 't_order_update_in_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
