<?php

namespace App\Models\Master\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class YmstpostModel
 * 
 * @package App\Models
 */
class YmstpostModel extends Model
{
    protected $table = 'm_ymstpost';
    protected $primaryKey = 'zip_code';
    public $incrementing = false;
    protected $fillable = [
        'reserve1',
        'zip_code',
        'reserve2',
        'cls_code1',
        'cls_code2',
        'apply_date',
        'post_type',
        'reserve3',
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

/* 
 */
}
