<?php

namespace App\Modules\Payment\Base;

/**
 * 出荷情報取得インターフェース
 */
interface ExportBillingDataInterface
{
    /**
     * @param int ( t_billing_outputs_id )
     * @param string ( template file path )
     * @param string  ( file save path output path )
     * @param int  ( account id )
     * @return mixed
    */
    public function execute($id,$templateFilePath,$savePath,$accountId);
}
