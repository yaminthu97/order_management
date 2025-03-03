<?php

namespace App\Models\Cc\Gfh1207;

use App\Enums\AlertCustTypeEnum;
use App\Enums\DeleteFlg;
use App\Enums\DmSendLetterFlgEnum;
use App\Enums\DmSendMailFlgEnum;
use App\Enums\SexTypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerModel extends Model
{
    use HasFactory;

    protected $table = 'm_cust';

    protected $primaryKey = 'm_cust_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';

    /**
     * キャストする属性の取得
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'delete_flg' => DeleteFlg::class,
            // 'sex_type' => SexTypeEnum::class,
            // 'alert_cust_type' => AlertCustTypeEnum::class,
            'discount_rate' => 'float', //gettypeではdoubleで返る。(https://www.php.net/manual/ja/function.gettype.php)
            // 'dm_send_letter_flg' => DmSendLetterFlgEnum::class,
            // 'dm_send_mail_flg' => DmSendMailFlgEnum::class,
        ];
    }

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 顧客別受注集計とのリレーション
     */
    public function custOrderSum()
    {
        return $this->hasOne(\App\Models\Cc\Base\CustOrderSumModel::class, 'm_cust_id', 'm_cust_id');
    }

    /**
     * 顧客別EC受注集計とのリレーション
     */
    public function custEcOrderSum()
    {
        return $this->hasMany(\App\Models\Cc\Base\CustEcOrderSumModel::class, 'm_cust_id', 'm_cust_id');
    }

    /**
     * 項目名称マスタとのリレーション(顧客ランク)
     */
    public function custRunk()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'm_cust_runk_id', 'm_itemname_types_id');
    }

    /**
     * 項目名称マスタとのリレーション(顧客区分)
     */
    public function customerType()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'customer_type', 'm_itemname_types_id');
    }

    /**
     * 削除済判定
     */
    public function isDeleted(): bool
    {
        return !is_null($this->delete_operator_id) && $this->delete_operator_id !== 0;
    }

    /**
     * 削除フラグの表示文字列
     */
    public function displayDeleteFlg(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return DeleteFlg::tryFrom($attribute['delete_flg'])?->label();
        });
    }

    /**
     * 要注意顧客区分の表示文字列
     */
    public function displayAlertCustType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return AlertCustTypeEnum::tryFrom($attribute['alert_cust_type'])?->label();
        });
    }

    /**
     * 性別の表示文字列
     */
    public function displaySexType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return SexTypeEnum::tryFrom($attribute['sex_type'])?->label();
        });
    }

    /**
     * DM送付方法の表示文字列
     */
    public function displayDmSendLetterFlg(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return DmSendLetterFlgEnum::tryFrom($attribute['dm_send_letter_flg'])?->label();
        });
    }

    /**
     * DM送付方法の表示文字列
     */
    public function displayDmSendMailFlg(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return DmSendMailFlgEnum::tryFrom($attribute['dm_send_mail_flg'])?->label();
        });
    }

    public function displayPostal(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            if (empty($attribute['postal'])) {
                return '';
            }
            return substr($attribute['postal'], 0, 3) . '-' . substr($attribute['postal'], 3, 4);
        });
    }

    public function displayDeletedLabel(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            if (is_null($attribute['delete_operator_id']) || $attribute['delete_operator_id'] === 0) {
                return '';
            }
            return '削除済み顧客';
        });
    }

    /**
     * %で表示する
     */
    public function displayDiscountRate(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            if($attribute['discount_rate'] == 0){
                return '';
            }
            // 小数点以下.00 ならば整数に変換
            $discountRate = $attribute['discount_rate'] == (int)$attribute['discount_rate'] ? (int)$attribute['discount_rate'] : $attribute['discount_rate'];
            return $discountRate . '%';
        });
    }

    /**
     * 顧客とのメールアドレス
     */
    public function email()
    {
        return $this->belongsTo(\App\Models\Cc\Base\CustEmailModel::class, 'm_cust_id', 'm_cust_id');
    }

    public function getEmailAttribute()
    {
        return $this->email()->value('email') ?? '';
    }

    /**
     * 顧客との電話番号
     */
    public function tel()
    {
        return $this->belongsTo(\App\Models\Cc\Base\CustTelModel::class, 'm_cust_id', 'm_cust_id');
    }

}
