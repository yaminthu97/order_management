<?php

namespace App\Modules\Ami\Base;

interface FindAmiPageAttachmentInterface
{
    /**
     * 検索処理
     */
    public function execute(int $id);
}
