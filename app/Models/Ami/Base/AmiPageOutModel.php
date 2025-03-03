<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiPageOutModel
 * 
 * @package App\Models
 */
class AmiPageOutModel extends Model
{
    protected $table = 'w_ami_page_out';
    protected $primaryKey = 'w_ami_page_out_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
