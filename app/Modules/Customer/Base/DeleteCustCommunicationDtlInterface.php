<?php

namespace App\Modules\Customer\Base;

interface DeleteCustCommunicationDtlInterface
{
    public function execute(string|int $id): bool;
}
