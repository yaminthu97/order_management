<?php


namespace App\Models\Goto\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoMailGetOrderEcModel
 *
 * @package App\Models
 */
class GotoMailGetOrderEcModel extends Model
{
    protected $table = 'm_goto_mail_get_order_ec';
    protected $primaryKey = 'm_batch_auto_command_id';
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
