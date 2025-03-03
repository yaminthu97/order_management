<?php

namespace App\Modules\Ami\Base;

interface SearchAttachmentitemModuleInterface
{
    /**
     * 取得処理
     */
    public function execute(array $conditions);
}