<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Base\YmsttimeModel;
use App\Modules\Master\Base\SaveYmsttimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SaveYmsttime implements SaveYmsttimeInterface
{
    public function execute(array $fillData): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($fillData) {
                $new = new YmsttimeModel();

                $new->insert($fillData);

                // 複数のモデルを更新する場合は、続けて記述する
                // 必要に応じてprivateメソッドとして切り出してもよいが、トランザクション内であるためトランザクションのネストに注意すること

                // 複数のモデルを更新したとしても、返却するのは、その処理の主体となるモデル
                return $new;
            });

            // トランザクション後もしくは別トランザクションで処理を行う場合は、ここに記述する

        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new;
    }
}
