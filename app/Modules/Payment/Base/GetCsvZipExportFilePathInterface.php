<?php

namespace App\Modules\Payment\Base;

/**
 * 出荷情報取得インターフェース
 */
interface GetCsvZipExportFilePathInterface
{
    /**
     * 出荷情報取得
     *
     * @param string
     */

    public function execute($accountCode, $batchType, $batchExecutionId);
}
