<?php


namespace App\Models\Ami\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AmiPageVariationChoiceModel
 * 
 * @package App\Models
 */
class AmiPageVariationChoiceModel extends Model
{
    protected $table = 'm_ami_page_variation_choice';
    protected $primaryKey = 'm_ami_page_variation_choice_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * ページバリエーションマスタとのリレーション
     */
    public function pageVariation()
    {
        return $this->belongsTo(\App\Models\Ami\Base\AmiPageVariationModel::class, 'm_ami_page_variation_id', 'm_ami_page_variation_id');
    }

    /**
     * ページマスタとのリレーション
     */
    public function page()
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
