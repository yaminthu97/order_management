<?php

namespace App\Models\Claim\Gfh1207;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ReceiptHdrModel
 *
 * @package App\Models
 */
class ReceiptHdrModel extends Model
{
    protected $table = 't_receipt_hdr';
    protected $primaryKey = 't_receipt_hdr_id';
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
     * 支払方法マスタとのリレーション
     */
    public function paymentType()
    {
        return $this->belongsTo(\App\Models\Master\Base\PaymentTypeModel::class, 'm_payment_types_id', 'm_payment_types_id');
    }

    /**
     * 受注基本とのリレーション
     */
    public function orderHdr()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /**
     * 領収書出力履歴とのリレーション
     */
    public function receiptOuput()
    {
        return $this->belongsTo(\App\Models\Claim\Gfh1207\ReceiptOutputModel::class, 't_receipt_hdr_id', 't_receipt_hdr_id');
    }
    /**
     * 登録ユーザ
     */
    public function entryOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'entry_operator_id', 'm_operators_id');
    }
    /**
     * 支払方法マスタとのリレーション
     */
    public function paymentTypes()
    {
        return $this->belongsTo(\App\Models\Master\Base\PaymentTypeModel::class, 'm_payment_types_id', 'm_payment_types_id');
    }
    public function payment()
    {
        return $this->belongsTo(\App\Models\Order\Base\PaymentModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }
}
