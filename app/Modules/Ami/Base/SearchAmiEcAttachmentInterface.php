<?php

namespace App\Modules\Ami\Base;

interface SearchAmiEcAttachmentInterface
{
    /**
     * 検索処理
     */
    public function execute(array $condtions=[], array $options=[]);
}
