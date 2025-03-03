<?php


namespace App\Models\Common\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NoticeModel
 *
 * @package App\Models
 */
class NoticeModel extends Model
{
    protected $table = 'm_notices';
    protected $primaryKey = 'm_notices_id';
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
