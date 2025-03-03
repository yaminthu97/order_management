<?php


namespace App\Models\Claim\Gfh1207;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ReceiptOutputModel
 *
 * @package App\Models
 */
class ReceiptOutputModel extends Model
{
    protected $table = 't_receipt_output';
    protected $primaryKey = 't_receipt_output_id';
    public $timestamps = false;

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
     * 領収書基本とのリレーション
     */
    public function receiptHdr()
    {
        return $this->belongsTo(\App\Models\Claim\Gfh1207\ReceiptHdrModel::class, 't_receipt_hdr_id', 't_receipt_hdr_id');
    }

    /**
     * 帳票テンプレートとのリレーション
     */
    public function template()
    {
        return $this->belongsTo(\App\Models\Master\Base\ReportTemplateModel::class, 'template_id', 'm_report_template_id');
    }
}
