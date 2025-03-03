<?php

namespace App\Modules\Ami\Base;

interface StoreAmiPageAttachmentInterface
{
    /**
     * 保存処理
     */
    public function execute(array $conditions);
}
