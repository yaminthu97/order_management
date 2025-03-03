<?php


namespace App\Models\Goto\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoWmsLsparkReceivingResultImportModel
 *
 * @package App\Models
 */
class GotoWmsLsparkReceivingResultImportModel extends Model
{
    protected $table = 'w_goto_wms_lspark_receiving_result_import';
    protected $primaryKey = 'w_goto_wms_lspark_receiving_result_import_id';
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
