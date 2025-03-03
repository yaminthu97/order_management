<?php

namespace App\Models\Master\Gfh1207;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WarehouseCalendarModel
 *
 * @package App\Models
 */
class WarehouseCalendarModel extends Model
{
    protected $table = 'm_warehouse_calendar';
    protected $primaryKey = 'm_warehouse_calendar_id';

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
        'm_warehouses_id',
        'calendar_year',
        'calendar_month',
        'month_days',
        'first',
        'second',
        'third',
        'fourth',
        'fifth',
        'sixth',
        'seventh',
        'eighth',
        'ninth',
        'tenth',
        'eleventh',
        'twelfth',
        'thirteenth',
        'fourteenth',
        'fifteenth',
        'sixteenth',
        'seventeenth',
        'eighteenth',
        'nineteenth',
        'twentieth',
        'twenty-first',
        'twenty-second',
        'twenty-third',
        'twenty-fourth',
        'twenty-fifth',
        'twenty-sixth',
        'twenty-seventh',
        'twenty-eighth',
        'twenty-ninth',
        'thirtieth',
        'thirty-first',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp'
    ];

    protected $attributes = [
        'delete_flg' => '0',
    ];

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 倉庫マスタとのリレーション
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse\Base\WarehouseModel::class, 'm_warehouses_id', 'm_warehouses_id');
    }
}
