<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderDtlNoshiModel
 * 
 * @package App\Models
 */
class OrderDtlNoshiModel extends Model
{
    protected $table = 't_order_dtl_noshi';
    protected $primaryKey = 't_order_dtl_noshi_id';

    protected $fillable = [
        'm_account_id',
        't_order_hdr_id',
        't_order_destination_id',
        'order_destination_seq',
        't_order_dtl_id',
        'order_dtl_seq',
        'ecs_id',
        'sell_cd',
        'count',
        'noshi_id',
        'noshi_detail_id',
        'noshi_type',
        'attachment_item_group_id',
        'noshi_file_name',
        'output_counter',
        'shared_flg',
        'omotegaki',
        'm_noshi_naming_pattern_id',
        'attach_flg',
        'company_name1',
        'company_name2',
        'company_name3',
        'company_name4',
        'company_name5',
        'section_name1',
        'section_name2',
        'section_name3',
        'section_name4',
        'section_name5',
        'title1',
        'title2',
        'title3',
        'title4',
        'title5',
        'firstname1',
        'firstname2',
        'firstname3',
        'firstname4',
        'firstname5',
        'name1',
        'name2',
        'name3',
        'name4',
        'name5',
        'ruby1',
        'ruby2',
        'ruby3',
        'ruby4',
        'ruby5',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
        'cancel_operator_id',
        'cancel_timestamp',
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

    /**
     * 熨斗とのリレーション
     */
    public function noshi()
    {
        return $this->belongsTo(\App\Models\Master\Base\NoshiModel::class, 'noshi_id', 'm_noshi_id');
    }

    /**
     * 熨斗詳細とのリレーション
     */
    public function noshiDetail()
    {
        return $this->belongsTo(\App\Models\Master\Base\NoshiDetailModel::class, 'noshi_detail_id', 'm_noshi_detail_id');
    }

    /*
     * 項目名称マスタとのリレーション（付属品グループ区分）
     */
    public function itemGroup()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'attachment_item_group_id', 'm_itemname_types_id');
    }

    /*
     * 熨斗名入れパターンとのリレーション
     */
    public function noshiNamingPattern()
    {
        return $this->belongsTo(\App\Models\Master\Base\NoshiNamingPatternModel::class, 'm_noshi_naming_pattern_id', 'm_noshi_naming_pattern_id');
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
