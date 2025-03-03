<?php

namespace App\Models\Cc\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CustEmailModel
 *
 * @package App\Models
 */
class CustEmailModel extends Model
{
    protected $table = 'm_cust_email';
    protected $primaryKey = 'm_cust_email_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';

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
        return $this->belongsTo(\App\Models\Cc\Base\CustModel::class, 'm_cust_id', 'm_cust_id');
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
     * 顧客とのリレーション
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\Cc\Gfh1207\CustomerModel::class, 'm_cust_id', 'm_cust_id');
    }
}
