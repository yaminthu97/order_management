<?php

namespace App\Models\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class YmsttimeModel
 *
 * @package App\Models
 */
class YmsttimeModel extends Model
{
    protected $table = 'm_ymsttime';
    public $incrementing = false;
    protected $fillable = [
        'from_base',
        'cls_code1',
        'reserve1',
        'delivery_days',
        'delivery_time',
        'apply_date',
        'time_type',
        'reserve2',
        'update_date',
    ];
    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
