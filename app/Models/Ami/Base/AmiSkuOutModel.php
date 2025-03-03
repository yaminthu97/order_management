<?php


namespace App\Models\Ami\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiSkuOutModel
 * 
 * @package App\Models
 */
class AmiSkuOutModel extends Model
{
    protected $table = 'w_ami_sku_out';
    protected $primaryKey = 'w_ami_sku_out_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
