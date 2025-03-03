<?php
namespace App\Modules\Order\Base;

/**
 * 受注配送先情報取得
 */
interface FindOrderDestinationInterface
{
    /**
     * 検索処理
     * @param array $conditions 検索条件
     * @param array $options 検索オプション
     */
    public function execute($id);
}