<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryDtlAttachmentItemModel
 * 
 * @package App\Models
 */
class DeliveryDtlAttachmentItemModel extends Model
{
    protected $table = 't_delivery_dtl_attachment_items';
    protected $primaryKey = 't_delivery_dtl_attachment_item_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /*
     * 付属品とのリレーション
     */
    public function amiAttachmentItem()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiAttachmentItemModel::class, 'attachment_item_id', 'm_ami_attachment_item_id');
    }
    /*
     * 項目名称マスタとのリレーション（付属品カテゴリ区分）
     */
    public function category()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'category_id', 'm_itemname_types_id');
    }
    /*
     * 受注明細付属品とのリレーション
     */
    public function orderDtlAttachmentItem()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderDtlAttachmentItemModel::class, 't_order_dtl_attachment_item_id', 't_order_dtl_attachment_item_id');
    }
}
