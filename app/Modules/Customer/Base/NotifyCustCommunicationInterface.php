<?php

namespace App\Modules\Customer\Base;

use Illuminate\Database\Eloquent\Model;

interface NotifyCustCommunicationInterface
{
    public function execute(array $fillData, array $exFillData, int|string|null $id): Model;
}
