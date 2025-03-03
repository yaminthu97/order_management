<?php

namespace App\Modules\Order\Base;

/**
 * Ecbeing受注データ取込インターフェース
 */
interface ImportEcbeingOrderDataInterface
{
    /**
     * Ecbeing受注データ取込
     *
     * @param string (orderTsvFilePath)
     * @param int (accountId)
     * @param string (account code)
     * @param string (batch type)
     * @param int string (batch id)
     * @param int (operators Id)
     */

    public function execute($orderTsvFilePath, $accountId, $accountCode, $batchType, $bathID ,$operatorsId);
}
