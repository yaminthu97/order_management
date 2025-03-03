<?php


namespace App\Models\Ami\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiPageInModel
 * 
 * @package App\Models
 */
class AmiPageInModel extends Model
{
    protected $table = 'w_ami_page_in';
    protected $primaryKey = 'w_ami_page_in_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
