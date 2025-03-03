<?php

namespace App\Models\Cc\Gfh1207;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CustCommunicationModel
 *
 * @package App\Models
 */
class CustCommunicationModel extends Model
{
    protected $table = 't_cust_communication';
    protected $primaryKey = 't_cust_communication_id';
    protected $guarded = [];

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';


    protected $fillable = [
        'm_account_id',
        'm_cust_id',
        't_order_hdr_id',
        'page_cd',
        'contact_way_type',
        'name_kanji',
        'name_kana',
        'tel',
        'email',
        'postal',
        'address1',
        'address2',
        'address3',
        'address4',
        'note',
        'title',
        'sales_channel',
        'inquiry_type',
        'open',
        'status',
        'category',
        'receive_detail',
        'receive_operator_id',
        'receive_datetime',
        'escalation_operator_id',
        'answer_detail',
        'answer_operator_id',
        'answer_datetime',
        'resolution_status',
        'entry_operator_id',
        'entry_timestamp',
    ];

    /*
     * 企業アカウントとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 顧客とのリレーション
     */
    public function cust()
    {
        return $this->belongsTo(\App\Models\Cc\Gfh1207\CustomerModel::class, 'm_cust_id', 'm_cust_id');
    }

    /**
     * 受注基本とのリレーション
     */
    public function orderHdr()
    {
        return $this->belongsTo(\App\Models\Order\Gfh1207\OrderHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 対応履歴詳細とのリレーション
     */
    public function custCommunicationDtl()
    {
        return $this->hasMany(\App\Models\Cc\Gfh1207\CustCommunicationDtlModel::class, 't_cust_communication_id', 't_cust_communication_id');
    }

    /**
     * 項目名称マスタ(顧客対応履ステータス)とのリレーション
     */
    public function custCommunicationStatus()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemNameTypeModel::class, 'status', 'm_itemname_types_id');
    }

    /*
     * 受信者
     */
    public function receiveOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'receive_operator_id', 'm_operators_id');
    }

    /*
     * エスカレーション担当者
     */
    public function escalationOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'escalation_operator_id', 'm_operators_id');
    }

    /*
     * 回答者
     */
    public function answerOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'answer_operator_id', 'm_operators_id');
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
     * 最新の対応履歴詳細
     */
    public function latestCustCommunicationDtl()
    {
        return $this->hasOne(\App\Models\Cc\Gfh1207\CustCommunicationDtlModel::class, 't_cust_communication_id', 't_cust_communication_id')
            ->latest('entry_timestamp');
    }

    /**
     * 最古の対応履歴詳細
     */
    public function oldestCustCommunicationDtl()
    {
        return $this->hasOne(\App\Models\Cc\Gfh1207\CustCommunicationDtlModel::class, 't_cust_communication_id', 't_cust_communication_id')
            ->oldest('entry_timestamp');
    }

    /**
     * 付属品マスタとのリレーション
     */
    public function inquiry()
    {
        return $this->hasOne(\App\Models\Master\Base\ItemnameTypeModel::class, 'm_itemname_types_id', 'inquiry_type');
    }

     /**
     * 付属品マスタとのリレーション
     */
    public function saleChannal()
    {
        return $this->hasOne(\App\Models\Master\Base\ItemnameTypeModel::class, 'm_itemname_types_id', 'sales_channel');
    }
}
