<?php

namespace App\Modules\Customer\Gfh1207;

use App\Models\Cc\Gfh1207\CustCommunicationModel;
use App\Modules\Customer\Base\NewCustCommunicationInterface;
use Illuminate\Database\Eloquent\Model;

class NewCustCommunication implements NewCustCommunicationInterface
{
    public function execute(array $fillData = [], array $exFillData = []): Model
    {
        $model = new CustCommunicationModel();
        $model->fill($fillData);
        foreach ($exFillData as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }
}
