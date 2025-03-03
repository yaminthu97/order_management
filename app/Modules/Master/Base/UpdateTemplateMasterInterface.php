<?php

namespace App\Modules\Master\Base;

interface UpdateTemplateMasterInterface
{
    public function execute(string|int $id, array $conditions);

}
