<?php

namespace App\Models\Goto\Gfh1207;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DepositorNumberModel
 *
 * @package App\Models
 */
class DepositorNumberModel extends Model
{
    protected $table = 'm_depositor_number';
    protected $primaryKey = 'm_depositor_number_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'entry_timestamp';
    public const UPDATED_AT = 'update_timestamp';

}
