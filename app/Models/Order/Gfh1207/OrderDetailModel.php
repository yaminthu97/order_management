<?php

namespace App\Models\Order\Gfh1207;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetailModel extends Model
{
    use HasFactory;

    /**
     * テーブル名称
     *
     * @var string
     */
    protected $table = 't_order_dtl';


    /**
     * テーブルの主キー
     *
     * @var string
     */
    protected $primaryKey = 't_order_dtl_id';

    protected $fillable = [
        'm_account_id',
        't_order_hdr_id',
        't_order_destination_id',
        'order_destination_seq',
        'order_dtl_seq',
        'ecs_id',
        'sell_id',
        'sell_cd',
        'sell_name',
        'order_sell_price',
        'order_cost',
        'order_time_sell_vol',
        'order_sell_vol',
        'tax_rate',
        'tax_price',
        'attachment_item_group_id',
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
}
