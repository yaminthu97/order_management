<?php


namespace App\Models\Goto\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoSystemCooperationHistoryLogModel
 *
 * @package App\Models
 */
class GotoSystemCooperationHistoryLogModel extends Model
{
    protected $table = 't_goto_system_cooperation_history_log';
    protected $primaryKey = 't_goto_system_cooperation_history_id';
    public $incrementing = false;
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
