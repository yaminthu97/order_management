<?php


namespace App\Models\Cc\Base;

use Exception;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CustOrderSumModel
 *
 * @package App\Models
 */
class CustOrderSumModel extends Model
{
    protected $table = 'm_cust_order_sum';
    protected $primaryKey = 'm_cust_order_sum_id';

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
     * 顧客とのリレーション
     */
    public function cust()
    {
        return $this->belongsTo(\App\Models\Cc\Base\CustModel::class, 'm_cust_id', 'm_cust_id');
    }

    /*
     * ECサイトマスタとのリレーション（初回購入店舗）
     */
    public function firstEcs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'first_ecs_id', 'm_ecs_id');
    }

    /*
     * ECサイトマスタとのリレーション（最新購入店舗）
     */
    public function newestEcs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'newest_ecs_id', 'm_ecs_id');
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
     * 三桁区切りで表示
     */
    public function displayTotalOrderMoney(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return number_format($attribute['total_order_money']);
        });
    }

    /**
     * 三桁区切りで表示
     */
    public function displayTotalUnbilledMoney(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return number_format($attribute['total_unbilled_money']);
        });
    }

    /**
     * 三桁区切りで表示
     */
    public function displayTotalUndepositedMoney(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return number_format($attribute['total_undeposited_money']);
        });
    }

    //「未請求金額」を減算し「未入金金額」を加算
    public function subTotalUnbilledAndAppendTotalUndeposited($value)
    {
        if (empty($value) || !is_numeric($value) || $value < 1) {
            throw new Exception('入金額 : ' . $value . ' が有効な値ではない。');
        }

        if (is_null($this->total_unbilled_money)) {
            throw new Exception('未請求金額が未定義です。');
        }

        if (is_null($this->total_undeposited_money)) {
            throw new Exception('未入金金額が未定義です。');
        }

        if ($this->total_unbilled_money < $value) {
            throw new Exception('入金額 : ' . $value . ' が未請求金額より多い。');
        }

        $dec = $this->decrement('total_unbilled_money', $value);

        if ($dec !== 1) {
            throw new Exception('total_unbilled_money 更新失敗。');
        }

        $inc = $this->increment('total_undeposited_money', $value);

        if ($inc !== 1) {
            throw new Exception('total_undeposited_money 更新失敗。');
        }
    }
}
