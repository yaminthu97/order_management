<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NoticeUserConfirmModel
 *
 * @package App\Models
 */
class NoticeUserConfirmModel extends Model
{
	protected $table = 't_notice_user_confirm';
	protected $primaryKey = 't_notice_user_confirm_id';
	public $timestamps = false;

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
