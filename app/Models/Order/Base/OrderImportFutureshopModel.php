<?php


namespace App\Models\Order\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderImportFutureshopModel
 * 
 * @package App\Models
 */
class OrderImportFutureshopModel extends Model
{
    protected $table = 'w_order_import_futureshop';
    protected $primaryKey = 'w_order_import_futureshop_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
