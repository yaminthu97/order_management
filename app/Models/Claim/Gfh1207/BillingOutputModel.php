<?php


namespace App\Models\Claim\Gfh1207;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
/**
 * Class BillingOutputModel
 *
 * @package App\Models
 */
class BillingOutputModel extends Model
{
    protected $table = 't_billing_outputs';
    protected $primaryKey = 't_billing_outputs_id';
    public $timestamps = false;

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    protected $fillable = [
        'billing_no',
        'm_account_id',
        't_billing_hdr_id',
        't_order_hdr_id',
        'cvs_barcode',
        'jp_ocr_code_1',
        'jp_ocr_code_2',
        'payment_due_date',
        'template_id',
        'is_available',
        'output_at',
        'is_output',
        'is_reprint',
        'is_remind',
        'entry_operator_id',
        'entry_timestamp',
        'update_operator_id',
        'update_timestamp',
    ];



    /**
     * 企業アカウントマスタとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /**
     * 受注基本とのリレーション
     */
    public function order()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 請求基本とのリレーション
     */
    public function billingHdr()
    {
        return $this->belongsTo(\App\Models\Claim\Gfh1207\BillingHdrModel::class, 't_billing_hdr_id', 't_billing_hdr_id');
    }

    /**
     * 帳票テンプレートとのリレーション
     */
    public function template()
    {
        return $this->belongsTo(\App\Models\Master\Base\ReportTemplateModel::class, 'template_id', 'm_report_template_id');
    }
}
