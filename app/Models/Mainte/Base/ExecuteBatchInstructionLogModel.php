<?php


namespace App\Models\Mainte\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExecuteBatchInstructionLogModel
 *
 * @package App\Models
 */
class ExecuteBatchInstructionLogModel extends Model
{
    protected $table = 't_execute_batch_instruction_log';
    protected $primaryKey = 't_execute_batch_instruction_id';
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
