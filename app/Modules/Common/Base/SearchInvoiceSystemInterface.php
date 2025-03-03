<?php

namespace App\Modules\Common\Base;

interface SearchInvoiceSystemInterface
{
    /**
     * 送り状システムマスタ取得
     * @param int $invoiceSystemId 請求システムID
     */
    public function execute(array $condtions = [], array $options = []);
}
