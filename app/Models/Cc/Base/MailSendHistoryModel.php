<?php


namespace App\Models\Cc\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MailSendHistoryModel
 * 
 * @package App\Models
 */
class MailSendHistoryModel extends Model
{
    protected $table = 't_mail_send_history';
    protected $primaryKey = 't_mail_send_history_id';

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
     * 出荷基本とのリレーション
     */
    public function deliveryHdr()
    {
        return $this->belongsTo(\App\Models\Order\Base\DeliHdrModel::class, 't_deli_hdr_id', 't_delivery_hdr_id');
    }

    /**
     * 顧客とのリレーション
     */
    public function cust()
    {
        return $this->belongsTo(\App\Models\Cc\Base\CustModel::class, 'm_cust_id', 'm_cust_id');
    }

    /**
     * メールテンプレートマスタとのリレーション
     */
    public function emailTemplates()
    {
        return $this->belongsTo(\App\Models\Master\Base\EmailTemplateModel::class, 'm_email_templates_id', 'm_email_templates_id');
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
