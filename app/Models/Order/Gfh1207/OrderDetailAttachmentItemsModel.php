<?php

namespace App\Models\Order\Gfh1207;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetailAttachmentItemsModel extends Model
{
    use HasFactory;

    /**
     * テーブル名称
     *
     * @var string
     */
    protected $table = 't_order_dtl_attachment_items';


    /**
     * テーブルの主キー
     *
     * @var string
     */
    protected $primaryKey = 't_order_dtl_attachment_item_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'm_account_id',
        't_order_hdr_id',
        't_order_destination_id',
        't_order_dtl_id',
        'sell_cd',
        'order_sell_vol',
        'attachment_item_id',
        'attachment_vol',
        'attachment_item_name',
        'group_id',
        'category_id',
        'display_flg',
        'invoice_flg',
        'attachment_item_cd',
        'entry_operator_id',
        'update_operator_id',
    ];

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';
}
