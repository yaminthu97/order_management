<?php


namespace App\Models\Cc\Base;

use App\Enums\AlertCustTypeEnum;
use App\Enums\DeleteFlg;
use App\Enums\DmSendLetterFlgEnum;
use App\Enums\DmSendMailFlgEnum;
use App\Enums\SexTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CustModel
 *
 * @package App\Models
 */
class CustModel extends Model
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

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    protected $fillable = [
        'name_sorting_flg',
        'm_cust_id',
        'cust_cd',
        'm_cust_runk_id',
        'name_kanji',
        'name_kana',
        'sex_type',
        'birthday',
        'tel1',
        'tel2',
        'tel3',
        'tel4',
        'fax',
        'postal',
        'address1',
        'address2',
        'address3',
        'address4',
        'address5',
        'corporate_kanji',
        'corporate_kana',
        'division_name',
        'corporate_tel',
        'email1',
        'email2',
        'email3',
        'email4',
        'email5',
        'alert_cust_type',
        'alert_cust_comment',
        'note',
        'reserve1',
        'reserve2',
        'reserve3',
        'reserve4',
        'reserve5',
        'reserve6',
        'reserve7',
        'reserve8',
        'reserve9',
        'reserve10',
        'reserve11',
        'reserve12',
        'reserve13',
        'reserve14',
        'reserve15',
        'reserve16',
        'reserve17',
        'reserve18',
        'reserve19',
        'reserve20',
        'operator_id',
        'delete_flg',
        'delete_operator_id',
    ];

    /*
     * 企業アカウントとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /*
     * 項目名称マスタとのリレーション（顧客ランク）
     */
    public function custRunk()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'm_cust_runk_id', 'm_itemname_types_id');
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
     * 削除ユーザ
     */
    public function deleteOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'delete_operator_id', 'm_operators_id');
    }

    /**
     * 顧客メールアドレスとのリレーション
     */
    public function custEmails()
    {
        return $this->hasMany(\App\Models\Cc\Base\CustEmailModel::class, 'm_cust_id', 'm_cust_id');
    }

    /**
     * 顧客電話番号とのリレーション
     */
    public function custTels()
    {
        return $this->hasMany(\App\Models\Cc\Base\CustTelModel::class, 'm_cust_id', 'm_cust_id');
    }

    /**
     * 郵便番号のミューテタ
     */
    public function postal(): Attribute
    {
        return Attribute::make(
            set:function ($value) {
                return str_replace('-', '', $value);
            },
            get:function ($value) {
                return substr($value, 0, 3) . '-' . substr($value, 3, 4);
            }
        );
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
            if(empty($attribute['postal'])){
                return '';
            }
            return substr($attribute['postal'], 0, 3) . '-' . substr($attribute['postal'], 3, 4);
        });
    }

    public function displayDeletedLabel(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            if(is_null($attribute['delete_operator_id']) || $attribute['delete_operator_id'] === 0){
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
}
