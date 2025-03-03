<?php

namespace App\Models\Master\Gfh1207;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderTagModel
 *
 * @package App\Models
 */
class OrderTagModel extends Model
{
    use HasFactory;
    protected $table = 'm_order_tag';
    protected $primaryKey = 'm_order_tag_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';

    protected $fillable = [
        'm_order_tag_id',
        'm_account_id',
        'm_order_tag_sort',
        'tag_name',
        'tag_display_name',
        'tag_icon',
        'tag_color',
        'font_color',
        'tag_context',
        'and_or',
        'auto_timming',
        'deli_stop_flg',
        'cond1_table_id',
        'cond1_column_id',
        'cond1_length_flg',
        'cond1_operator',
        'cond1_value',
        'cond2_table_id',
        'cond2_column_id',
        'cond2_length_flg',
        'cond2_operator',
        'cond2_value',
        'cond3_table_id',
        'cond3_column_id',
        'cond3_length_flg',
        'cond3_operator',
        'cond3_value',
        'cond4_table_id',
        'cond4_column_id',
        'cond4_length_flg',
        'cond4_operator',
        'cond4_value',
        'cond5_table_id',
        'cond5_column_id',
        'cond5_length_flg',
        'cond5_operator',
        'cond5_value',
        'cond6_table_id',
        'cond6_column_id',
        'cond6_length_flg',
        'cond6_operator',
        'cond6_value',
        'cond7_table_id',
        'cond7_column_id',
        'cond7_length_flg',
        'cond7_operator',
        'cond7_value',
        'cond8_table_id',
        'cond8_column_id',
        'cond8_length_flg',
        'cond8_operator',
        'cond8_value',
        'cond9_table_id',
        'cond9_column_id',
        'cond9_length_flg',
        'cond9_operator',
        'cond9_value',
        'cond10_table_id',
        'cond10_column_id',
        'cond10_length_flg',
        'cond10_operator',
        'cond10_value',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
    ];

    /*
     * 企業アカウントとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
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

    public function displayDeliStopFlgName(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            if($attribute['deli_stop_flg'] == '-1') {
                return '(未設定)';
            }
            return \App\Enums\ProgressTypeEnum::tryFrom($attribute['deli_stop_flg'])?->label();
        });
    }

    public function displayAutoTimmingName(): Attribute
    {
        return Attribute::make(function ($value, $attribute) {
            return \App\Modules\Order\Gfh1207\Enums\AutoTimmingEnum::tryFrom($attribute['auto_timming'])?->label();
        });
    }
}
