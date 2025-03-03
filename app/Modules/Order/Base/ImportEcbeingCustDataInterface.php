<?php

namespace App\Modules\Order\Base;

/**
 * Ecbeing顧客データ取込インターフェース
 */
interface ImportEcbeingCustDataInterface
{
    /**
     * Ecbeing顧客データ取込
     *
     * @param string (customerTsvFilePath)
     * @param int (accountId)
     * @param string (account code)
     * @param string (batch type)
     * @param int  (batch id)
     * @param int  (operators id)
     */

    public function execute($customerTsvFilePath, $accountId, $accountCode, $batchType, $bathID, $operatorsId);
}
