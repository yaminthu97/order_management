<?php

namespace App\Modules\Sample\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Base\PrefecturalModel;
use Illuminate\Database\Eloquent\Collection;
use PhpParser\Node\Expr\AssignOp\Mod;

class GetSamplePrefectural implements GetSamplePrefecturalInterface
{
    public function execute(): Collection
    {
        ModuleStarted::dispatch(__CLASS__, []);

        try{
            $query = PrefecturalModel::query();

            $ressult = $query->get();
        }catch(\Exception $e){
            ModuleFailed::dispatch(__CLASS__, [], $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, $ressult->toArray());
        return $ressult;
    }
}
