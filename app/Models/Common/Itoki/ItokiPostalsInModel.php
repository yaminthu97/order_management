<?php


namespace App\Models\Common\Itoki;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ItokiPostalsInModel
 *
 * @package App\Models
 */
class ItokiPostalsInModel extends Model
{
    protected $table = 'w_itoki_postals_in';
    protected $primaryKey = 'w_itoki_postals_in_id';
    protected $connection = 'global';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
