<?php


namespace App\Models\Order\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliReportOutModel
 * 
 * @package App\Models
 */
class DeliReportOutModel extends Model
{
    protected $table = 'w_deli_report_out';
    protected $primaryKey = 't_credit_cancel_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
