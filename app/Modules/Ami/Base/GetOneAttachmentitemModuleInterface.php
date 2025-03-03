<?php

namespace App\Modules\Ami\Base;

interface GetOneAttachmentitemModuleInterface
{
    /**
     * 取得処理(1件)
     */
    public function execute($id);
}