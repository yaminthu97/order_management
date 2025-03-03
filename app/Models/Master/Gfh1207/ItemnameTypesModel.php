<?php

namespace App\Models\Master\Gfh1207;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemnameTypesModel extends Model
{
    use HasFactory;

    /**
     * テーブル名称
     *
     * @var string
     */
    protected $table = 'm_itemname_types';


    /**
     * テーブルの主キー
     *
     * @var string
     */
    protected $primaryKey = 'm_itemname_types_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
