<?php

namespace App\Modules\Ami\Base;

interface DeleteAmiPageAttachmentInterface
{
    /**
     * 保存処理
     */
    public function execute(int $id);
}
