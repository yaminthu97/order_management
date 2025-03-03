<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderDtlAttachmentItemsSaveModel
 * 
 * @package App\Models
 */
class OrderDtlAttachmentItemsSaveModel extends Model
{
    protected $table = 't_order_dtl_attachment_items_save';
    protected $primaryKey = 't_order_dtl_attachment_item_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
