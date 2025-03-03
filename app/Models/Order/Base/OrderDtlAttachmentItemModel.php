<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderDtlAttachmentItemModel
 * 
 * @package App\Models
 */
class OrderDtlAttachmentItemModel extends Model
{
    protected $table = 't_order_dtl_attachment_items';
    protected $primaryKey = 't_order_dtl_attachment_item_id';

    protected $fillable = [
        'm_account_id',
        't_order_hdr_id',
        't_order_destination_id',
        'order_destination_seq',
        't_order_dtl_id',
        'order_dtl_seq',
        'ecs_id',
        'sell_cd',
        'order_sell_vol',
        'attachment_item_id',
        'attachment_item_cd',
        'attachment_item_name',
        'attachment_vol',
        'group_id',
        'category_id',
        'display_flg',
        'invoice_flg',
        'entry_operator_id',
        'update_operator_id'
    ];

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /*
     * 企業アカウントとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 受注基本とのリレーション
     */
    public function orderHdr()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 受注配送先とのリレーション
     */
    public function orderDestination()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderDestinationModel::class, 't_order_destination_id', 't_order_destination_id');
    }

    /**
     * 受注明細とのリレーション
     */
    public function orderDtl()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderDtlModel::class, 't_order_dtl_id', 't_order_dtl_id');
    }

    /*
     * ECサイトマスタテーブル
     */
    public function ecs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'ecs_id', 'm_ecs_id');
    }

    /*
     * 付属品とのリレーション
     */
    public function amiAttachmentItem()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiAttachmentItemModel::class, 'attachment_item_id', 'm_ami_attachment_item_id');
    }

    /*
     * 項目名称マスタとのリレーション（付属品グループ区分）
     */
    public function group()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'group_id', 'm_itemname_types_id');
    }

    /*
     * 項目名称マスタとのリレーション（付属品カテゴリ区分）
     */
    public function category()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'category_id', 'm_itemname_types_id');
    }

    /*
     * 出荷基本とのリレーション
     */
    public function deliveryHdr()
    {
        return $this->belongsTo(\App\Models\Order\Base\DeliveryHdrModel::class, 't_deli_hdr_id', 't_delivery_hdr_id');
    }

    /**
     * 登録ユーザ
     */
    public function entryOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'entry_operator_id', 'm_operators_id');
    }

    /**
     * 更新ユーザ
     */
    public function updateOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'update_operator_id', 'm_operators_id');
    }

    /**
     * 取消ユーザ
     */
    public function cancelOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'cancel_operator_id', 'm_operators_id');
    }
}
