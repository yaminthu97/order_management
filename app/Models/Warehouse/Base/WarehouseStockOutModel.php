<?php


namespace App\Models\Warehouse\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WarehouseStockOutModel
 * 
 * @package App\Models
 */
class WarehouseStockOutModel extends Model
{
    protected $table = 'w_warehouse_stock_out';
    protected $primaryKey = 'w_warehouse_stock_out_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
