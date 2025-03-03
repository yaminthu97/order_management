<?php


namespace App\Models\Common\Itoki;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ItokiSkuInModel
 *
 * @package App\Models
 */
class ItokiSkuInModel extends Model
{
    protected $table = 'w_itoki_sku_in';
    protected $primaryKey = 'w_ami_itoki_sku_in_id';
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
