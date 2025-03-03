<?php

namespace App\Modules\Customer\Base;

use Illuminate\Database\Eloquent\Model;

interface NewCustCommunicationInterface
{
    public function execute(array $fillData = [], array $exFillData = []): Model;
}
