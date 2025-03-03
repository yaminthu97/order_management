<?php

namespace App\Modules\Order\Gfh1207;

use App\Modules\Order\Base\GetSecurityValueInterface;

/**
 * パラメータデータをMD5で暗号化するモジュール
 */
class GetSecurityValue implements GetSecurityValueInterface
{
    /**
     * パラメータデータをMD5で暗号化する
     */
    public function execute($key, $apiKey)
    {
        return md5($key . $apiKey);
    }
}
