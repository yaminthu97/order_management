<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AccountModel
 *
 * @package App\Models
 */
class AccountModel extends Model
{
    use HasFactory;
    protected $table = 'm_account';
    protected $primaryKey = 'm_account_id';

    protected $connection = 'global';

    public $timestamps = false;
    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

}
