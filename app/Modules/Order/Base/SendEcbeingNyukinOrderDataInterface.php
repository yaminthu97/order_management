<?php

namespace App\Modules\Order\Base;

/**
 * 入金・受注修正データ出力インターフェース
 */
interface SendEcbeingNyukinOrderDataInterface
{   
    /**
     * 入金・受注修正データ出力
     * 
     *
     * @param array (argument)
     */
    public function execute($argument);
}
