<?php
namespace App\Modules\Billing\Base;

/**
 * 見積書・納品書・請求書検索
 */
interface SearchExcelReportInterface
{
    /**
     * 検索処理
     * @param array $conditions 検索条件
     * @param array $options 検索オプション
     */
    public function execute(array $conditions, array $options = []);
}