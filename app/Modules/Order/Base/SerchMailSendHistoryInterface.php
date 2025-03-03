<?php
namespace App\Modules\Order\Base;


interface SerchMailSendHistoryInterface
{
    /**
     * 検索処理
     */
    public function execute(array $conditions, array $options = []);
}
