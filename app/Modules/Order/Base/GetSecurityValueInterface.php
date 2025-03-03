<?php

namespace App\Modules\Order\Base;

/**
 * パラメータデータをMD5で暗号化するインターフェース
 */
interface GetSecurityValueInterface
{
    /**
     * パラメータデータをMD5で暗号化する
     *
     * @param string
     */

    public function execute($key, $apiKey);
}
