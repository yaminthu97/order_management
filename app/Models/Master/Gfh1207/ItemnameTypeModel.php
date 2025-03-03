<?php


namespace App\Models\Master\Gfh1207;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class ItemnameTypeModel
 *
 * @package App\Models
 */
class ItemnameTypeModel extends Model
{
    use HasFactory;
    protected $table = 'm_itemname_types';
    protected $primaryKey = 'm_itemname_types_id';

    protected $fillable = [
        'm_account_id',
        'delete_flg',
        'm_itemname_type',
        'm_itemname_type_code',
        'm_itemname_type_name',
        'm_itemname_type_sort',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
    ];
    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 削除フラグの表示文字列
     */
    public function displayDeleteFlg(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return \App\Enums\DeleteFlg::tryFrom($attribute['delete_flg'])?->label();
        });
    }

    public function displayItemnameType(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return \App\Enums\ItemNameType::tryFrom($attribute['m_itemname_type'])?->label();
        });
    }
}
