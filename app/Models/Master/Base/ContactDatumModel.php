<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ContactDatumModel
 *
 * @package App\Models
 */
class ContactDatumModel extends Model
{
    protected $table = 't_contact_data';
    protected $primaryKey = 't_contact_data_id';
    protected $connection = 'global';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 社員マスタとのリレーション
     */
    public function operator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'm_operators_id', 'm_operators_id');
    }

}
