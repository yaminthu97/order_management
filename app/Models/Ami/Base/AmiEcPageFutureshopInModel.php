<?php


namespace App\Models\Ami\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiEcPageFutureshopInModel
 * 
 * @package App\Models
 */
class AmiEcPageFutureshopInModel extends Model
{
    protected $table = 'w_ami_ec_page_futureshop_in';
    protected $primaryKey = 'w_ami_ec_page_futureshop_in_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

/* 
 */

    protected $hidden = [
        'futureshop_csv_yami_shi_password'
    ];

/* 
 */
}
