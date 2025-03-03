<?php
namespace App\Modules\Sample\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Cc\Base\CustModel;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class NotifySample implements NotifySampleInterface
{
    public function execute(array $fillData, array $exFillData, int|string|null $id): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try{
            $model = CustModel::findOrNew($id);
            $model->fill($fillData);

            // fillできないデータを設定
            // if(isset($exFillData['xxxx'])){
            //     $new->xxxx = $exFillData['xxxx'];
            // }
        }catch(Throwable $e){
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$model->toArray()]);
        return $model;
    }
}
