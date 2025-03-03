<?php


namespace App\Models\Master\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailTemplatesUseOrderStatusModel
 *
 * @package App\Models
 */
class EmailTemplatesUseOrderStatusModel extends Model
{
    protected $table = 'm_email_templates_use_order_status';
    protected $primaryKey = 'm_email_templates_use_order_status_id';

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
     * メールテンプレートマスタとのリレーション
     */
    public function emailTemplate()
    {
        return $this->belongsTo(\App\Models\Master\Base\EmailTemplateModel::class, 'm_email_templates_id', 'm_email_templates_id');
    }

}
