<?php

namespace App\Modules\Order\Base;

/**
 *  Call Ecbeing Api Interface
 */
interface CallEcbeingApiInterface
{
    /* @param Client $client
     * @param ApiNameListEnum $apiName
     * @param string $apiInfoValue
     * @return mixed
     * @throws Exception
     */

    public function execute($key, $client, $apiName, $apiInfoValue);
}
