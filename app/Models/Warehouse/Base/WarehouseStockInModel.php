<?php


namespace App\Models\Warehouse\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WarehouseStockInModel
 * 
 * @package App\Models
 */
class WarehouseStockInModel extends Model
{
    protected $table = 'w_warehouse_stock_in';
    protected $primaryKey = 'w_warehouse_stock_in_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
