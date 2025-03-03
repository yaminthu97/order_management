<?php
namespace App\Modules\Payment\Base;

/**
 * 請求書データ作成インターフェース
 */
interface CreateBillingDataInterface
{
    /**
     * 請求書データ作成
     *
     */
    public function execute($billingOutputId,$batchExecute,$tempPdfFilePath);
}