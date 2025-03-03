<?php


namespace App\Models\Goto\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoSemdmailReceptModel
 *
 * @package App\Models
 */
class GotoSemdmailReceptModel extends Model
{
    protected $table = 't_goto_semdmail_recept';
    protected $primaryKey = 't_goto_semdmail_recept_id';
    protected $connection = 'global';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    protected $hidden = [
        'smtp_password'
    ];

    /*
     * 企業アカウントとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /*
     * ECサイトマスタとのリレーション
     */
    public function ecs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'm_ecs_id', 'm_ecs_id');
    }

    /**
     * 利用者ID
     */
    public function requestOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'request_operator_id', 'm_operators_id');
    }

    /**
     * 登録ユーザ
     */
    public function entryOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'entry_operator_id', 'm_operators_id');
    }

    /**
     * 更新ユーザ
     */
    public function updateOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'update_operator_id', 'm_operators_id');
    }
}
