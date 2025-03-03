<?php


namespace App\Models\Common\Itoki;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ItokiDeliAnswerInModel
 *
 * @package App\Models
 */
class ItokiDeliAnswerInModel extends Model
{
    protected $table = 'w_itoki_deli_answer_in';
    protected $primaryKey = 'w_itoki_deli_answer_in_id';
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
