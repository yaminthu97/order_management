<?php
namespace App\Modules\Sample\Base;

use App\Models\Cc\Base\CustModel;
use Illuminate\Database\Eloquent\Model;

class NewSample implements NewSampleInterface
{
    public function execute(array $fillData=[], array $exFillData=[]):Model
    {
        $model = new CustModel();
        $model->fill($fillData);
        foreach ($exFillData as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }
}
