<?php


namespace App\Models\Goto\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoEcModel
 *
 * @package App\Models
 */
class GotoEcsModel extends Model
{
    protected $table = 'm_goto_ecs';
    protected $primaryKey = 'm_goto_ecs_id';
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
        'ftp_server_password',
        'yahoo_order_api_secret_file',
        'yahoo_refresh_token',
        'yahoo_access_token',
        'amazon_mws_auth_token',
        'amazon_mws_order_next_token',
        'amazon_sp_api_client_secret',
        'amazon_sp_api_refresh_token',
        'amazon_sp_api_access_token',
        'ecbeing_ftp_secret_file',
        'shopify_api_access_token'
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
     * 利用者
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
