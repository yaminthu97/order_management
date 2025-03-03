<?php


namespace App\Models\Order\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PurchaseExternalCooperationOutModel
 * 
 * @package App\Models
 */
class PurchaseExternalCooperationOutModel extends Model
{
    protected $table = 'w_purchase_external_cooperation_out';
    protected $primaryKey = 'w_purchase_external_cooperation_out_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
