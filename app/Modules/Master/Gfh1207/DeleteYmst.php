<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Base\YmstpostModel;
use App\Models\Master\Base\YmsttimeModel;
use App\Modules\Master\Base\DeleteYmstInterface;
use Illuminate\Support\Facades\DB;

class DeleteYmst implements DeleteYmstInterface
{
    private const PRIVATE_THROW_ERR_CODE = -1;
    public function execute(array $fillData): bool
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {
            // トランザクションを張る
            $deleted = DB::transaction(function () use ($fillData) {
                // Deleting records from both tables
                $del_time = YmsttimeModel::query()->delete(); // Deletes from YmsttimeModel
                $del_post = YmstpostModel::query()->delete(); // Deletes from YmstpostModel

                // Return the counts of deleted records
                return [$del_time, $del_post];
            });



        } catch (\Exception $e) {

            ModuleFailed::dispatch(__CLASS__, compact('fillData'), $e);
            throw new \Exception(__('messages.error.process_failed', ['process' => '削除処理']), self::PRIVATE_THROW_ERR_CODE);
        }

        ModuleCompleted::dispatch(__CLASS__, $deleted);
        return true;
    }

}
