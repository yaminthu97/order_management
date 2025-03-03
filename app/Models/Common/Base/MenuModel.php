<?php


namespace App\Models\Common\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MenuModel
 *
 * @package App\Models
 */
class MenuModel extends Model
{
    protected $table = 'm_menus';
    protected $primaryKey = 'm_menus_id';
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
