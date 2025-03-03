<?php


namespace App\Models\Ami\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiEcPageRakutenImageOutModel
 * 
 * @package App\Models
 */
class AmiEcPageRakutenImageOutModel extends Model
{
    protected $table = 'w_ami_ec_page_rakuten_image_out';
    protected $primaryKey = 'w_ami_ec_page_rakuten_image_out_id';
    public $incrementing = false;

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
