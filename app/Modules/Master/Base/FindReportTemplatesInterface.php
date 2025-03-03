<?php
namespace App\Modules\Master\Base;

/**
 * 見積書・納品書・請求書取得
 */
interface FindReportTemplatesInterface
{
    /**
     * 検索処理
     * @param array $conditions 検索条件
     * @param array $options 検索オプション
     */
    public function execute($id);
}