<?php


namespace App\Models\Goto\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoFlashOrderSkuModel
 *
 * @package App\Models
 */
class GotoFlashOrderSkuModel extends Model
{
    protected $table = 'w_goto_flash_order_sku';
    protected $primaryKey = 'w_goto_flash_order_sku_id';
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
