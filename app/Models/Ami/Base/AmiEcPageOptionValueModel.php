<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiEcPageOptionValueModel
 * 
 * @package App\Models
 */
class AmiEcPageOptionValueModel extends Model
{
    protected $table = 'm_ami_ec_page_option_value';
    protected $primaryKey = 'm_ami_ec_page_option_value_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * ECページマスタとのリレーション
     */
    public function amiEcPage()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiEcPageModel::class, 'm_ami_ec_page_id', 'm_ami_ec_page_id');
    }

    /**
     * ECページオプション選択肢マスタとのリレーション
     */
    public function amiPageOption()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiEcPageOptionModel::class, 'm_ami_page_option_id', 'm_ami_page_option_id');
    }

    /**
     * ページマスタとのリレーション
     */
    public function amiPage()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiPageModel::class, 'm_ami_page_id', 'm_ami_page_id');
    }

    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }
}
