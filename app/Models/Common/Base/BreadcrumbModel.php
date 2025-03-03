<?php


namespace App\Models\Common\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BreadcrumbModel
 *
 * @package App\Models
 */
class BreadcrumbModel extends Model
{
    protected $table = 'm_breadcrumb';
    protected $primaryKey = 'm_breadcrumb_id';
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
