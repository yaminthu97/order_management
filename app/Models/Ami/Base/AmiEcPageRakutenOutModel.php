<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiEcPageRakutenOutModel
 * 
 * @package App\Models
 */
class AmiEcPageRakutenOutModel extends Model
{
    protected $table = 'w_ami_ec_page_rakuten_out';
    protected $primaryKey = 'w_ami_ec_page_rakuten_out_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
