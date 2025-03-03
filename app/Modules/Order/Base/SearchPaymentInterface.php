<?php
namespace App\Modules\Order\Base;


interface SearchPaymentInterface
{
    /**
     * 検索処理
     * @param array $conditions 検索条件
     * @param array $options 検索オプション
     */
    public function execute(array $conditions, array $options = []);
}
