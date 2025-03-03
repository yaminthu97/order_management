<?php


namespace App\Models\Ami\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiEcPageYahooInModel
 * 
 * @package App\Models
 */
class AmiEcPageYahooInModel extends Model
{
    protected $table = 'w_ami_ec_page_yahoo_in';
    protected $primaryKey = 'w_ami_ec_page_yahoo_in_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
