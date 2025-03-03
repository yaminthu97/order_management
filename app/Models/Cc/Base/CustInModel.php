<?php


namespace App\Models\Cc\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CustInModel
 * 
 * @package App\Models
 */
class CustInModel extends Model
{
    protected $table = 'w_cust_in';
    protected $primaryKey = 'w_cust_in_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
