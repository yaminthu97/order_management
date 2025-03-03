<?php


namespace App\Models\Cc\Gfh1207;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CustCommunicationDtlModel
 *
 * @package App\Models
 */
class CustCommunicationDtlModel extends Model
{
    protected $table = 't_cust_communication_dtl';
    protected $primaryKey = 't_cust_communication_dtl_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * 顧客対応履歴とのリレーション
     */
    public function custCommunication()
    {
        return $this->belongsTo(\App\Models\Cc\Gfh1207\CustCommunicationModel::class, 't_cust_communication_id', 't_cust_communication_id');
    }

    /*
     * 企業アカウントとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
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
}
