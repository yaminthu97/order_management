<?php


namespace App\Models\Order\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DestinationModel
 * 
 * @package App\Models
 */
class DestinationModel extends Model
{
    use HasFactory;
    protected $table = 'm_destinations';
    protected $primaryKey = 'm_destination_id';

    protected $fillable = [
        'm_destination_id',
        'm_account_id',
        'delete_flg',
        'cust_id',
        'destination_name',
        'destination_name_kana',
        'destination_tel',
        'destination_postal',
        'destination_address1',
        'destination_address2',
        'destination_address3',
        'destination_address4',
        'destination_company_name',
        'destination_division_name',
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
     * 顧客とのリレーション
     */
    public function cust()
    {
        return $this->belongsTo(\App\Models\Cc\Base\CustModel::class, 'cust_id', 'm_cust_id');
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
