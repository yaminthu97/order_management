<?php


namespace App\Models\Common\Itoki;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ItokiOrderOutEditModel
 *
 * @package App\Models
 */
class ItokiOrderOutEditModel extends Model
{
    protected $table = 'w_itoki_order_out_edit';
    protected $primaryKey = 'w_itoki_order_out_edit_id';
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
