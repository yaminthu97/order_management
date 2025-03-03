<?php

namespace App\Modules\Ami\Base;

interface UpdateAmiPageAttachmentInterface
{
    /**
     * 保存処理
     */
    public function execute(string|int $id, array $conditions);
}
