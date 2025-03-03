<?php
namespace App\Modules\Sample\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\ModuleException;
use App\Models\Cc\Base\CustModel;
use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteSample implements DeleteSampleInterface
{
    public function __construct(
        protected EsmSessionManager $sessionManager
    )
    {}

    public function execute(string|int $id):bool
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try {
            // トランザクション開始
            DB::transaction(function () use ($id) {
                $sample = CustModel::findOrFail($id);

                // 削除前チェックはそれぞれの機能ごとに検討する。Esm2.0の標準処理を参考にすること。
                if($sample->isDeleted()){
                    throw new ModuleException(__('messages.error.data_already_deleted', ['data' => 'サンプル情報', 'id' => $id]));
                }

                // 論理削除となる扱いは、それぞれの機能ごとに検討する。
                // delete_flgを立てるのか、delete_operator_idを設定するのか。Esm2.0の標準処理を参考にすること。
                $sample->delete_operator_id = $this->sessionManager->getOperatorId();
                $sample->delete_timestamp = now();
                $sample->save();

                // 複数のモデルが関連する場合は、続けて記述する
            });
        } catch (Throwable $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$id]);
        return true;
    }
}
