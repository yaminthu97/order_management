<?php

namespace App\Models\Master\Gfh1207;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PaymentTypeModel
 *
 * @package App\Models
 */
class PaymentTypeModel extends Model
{
    use HasFactory;
    protected $table = 'm_payment_types';
    protected $primaryKey = 'm_payment_types_id';

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
        'delete_flg',
        'm_payment_types_name',
        'm_payment_types_code',
        'payment_type',
        'delivery_condition',
        'settlement_management_url',
        'attachment_form',
        'display_sum',
        'display_payment_type',
        'm_payment_types_sort',
        'atobarai_com_cooperation_type',
        'atobarai_com_url',
        'atobarai_com_acceptance_company_id',
        'atobarai_com_apiuser_id',
        'payment_fee',
        'finance_code',
        'cvs_company_code',
        'jp_account_num',
        'bank_code',
        'bank_shop_code',
        'bank_name',
        'bank_shop_name',
        'bank_account_type',
        'bank_account_num',
        'bank_account_name',
        'bank_account_name_kana',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
    ];

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
