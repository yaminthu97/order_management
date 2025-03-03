<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EcModel
 *
 * @package App\Models
 */
class EcsModel extends Model
{
    use HasFactory;
    protected $table = 'm_ecs';
    protected $primaryKey = 'm_ecs_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    protected $hidden = [
        'receiving_email_server_password',
        'send_email_server_password',
        'accept_orders_receiving_email_server_password',
        'accept_orders_send_email_server_password'
    ];

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }



}
