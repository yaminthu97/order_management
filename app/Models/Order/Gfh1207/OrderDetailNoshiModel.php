<?php

namespace App\Models\Order\Gfh1207;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetailNoshiModel extends Model
{
    use HasFactory;

    /**
     * テーブル名称
     *
     * @var string
     */
    protected $table = 't_order_dtl_noshi';


    /**
     * テーブルの主キー
     *
     * @var string
     */
    protected $primaryKey = 't_order_dtl_noshi_id';

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
        'noshi_id',
        'omotegaki',
        'company_name1',
        'title1',
        'name1',
        'name2',
        'count',
        'attach_flg',
        'm_noshi_naming_pattern_id',
        'noshi_file_name',
        'noshi_detail_id',
        'attachment_item_group_id',
        'template_file_name',
        'noshi_type',
        'ecs_id',
        'order_destination_seq',
        'order_dtl_seq',
        'entry_operator_id',
        'update_operator_id',
    ];

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';
}
