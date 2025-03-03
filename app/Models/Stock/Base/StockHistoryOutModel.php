<?php


namespace App\Models\Stock\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class StockHistoryOutModel
 * 
 * @package App\Models
 */
class StockHistoryOutModel extends Model
{
    protected $table = 'w_stock_history_out';
    protected $primaryKey = 'w_stock_history_out_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
