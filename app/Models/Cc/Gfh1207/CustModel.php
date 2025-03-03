<?php

namespace App\Models\Cc\Gfh1207;

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

    protected $fillable = [
        'delete_flg',
        'm_account_id',
        'm_cust_id',
        'cust_cd',
        'tel1',
        'tel2',
        'tel3',
        'tel4',
        'fax',
        'name_kanji',
        'name_kana',
        'postal',
        'address1',
        'address2',
        'address3',
        'address4',
        'email1',
        'email2',
        'email3',
        'email4',
        'email5',
        'note',
        'sex_type',
        'birthday',
        'corporate_kanji',
        'corporate_kana',
        'division_name',
        'corporate_tel',
        'm_cust_runk_id',
        'alert_cust_type',
        'alert_cust_comment',
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
        'customer_type',
        'discount_rate',
        'dm_send_letter_flg',
        'dm_send_mail_flg',
        'entry_operator_id',
        'update_operator_id',
        'delete_operator_id',
        'delete_timestamp',
        'update_operator_id'
    ];

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
     * DM送付方法の表示文字列
     */
    public function displayNote(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            if (empty($attribute['note'])) {
                return '';
            }
            if (mb_strlen($attribute['note'], 'UTF8') > 20) {
                return mb_substr($attribute['note'], 0, 20, 'UTF8') . '...';
            }
            return $attribute['note'];
        });
    }

    public function displayAddress2(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            if (empty($attribute['address2'])) {
                return '';
            }

            if (mb_strlen($attribute['address2'], 'UTF8') > 30) {
                return mb_substr($attribute['address2'], 0, 30, 'UTF8') . '...';
            }

            return $attribute['address2'];
        });
    }
    
    /**
     * 項目名称マスタとのリレーション(顧客区分)
     */
    public function customerType()
    {
        return $this->belongsTo(\App\Models\Master\Base\ItemnameTypeModel::class, 'customer_type', 'm_itemname_types_id');
    }

    /**
     * 顧客別受注集計とのリレーション
     */
    public function custOrderSum()
    {
        return $this->hasOne(\App\Models\Cc\Base\CustOrderSumModel::class, 'm_cust_id', 'm_cust_id');
    }
}
