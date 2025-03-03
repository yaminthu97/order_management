<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Warehouse\Gfh1207\WarehouseModel;
use App\Modules\Master\Base\StoreWarehousesInterface;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\DB;

class StoreWarehouses implements StoreWarehousesInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    public function __construct(
        EsmSessionManager $esmSessionManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute(array $fillData, array $exFillData)
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData', 'exFillData'));

        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($fillData, $exFillData) {
                $operatorId = $this->esmSessionManager->getOperatorId();

                $new = new WarehouseModel();

                $errors = [];
                // fillできるデータを設定
                $new->fill($fillData);

                $new->delete_flg = 0;
                $new->base_delivery_type = 1;
                $new->entry_operator_id = $operatorId;
                $new->update_operator_id = $operatorId;

                // fillできないデータを設定
                if (isset($exFillData['m_account_id'])) {
                    $new->m_account_id = $exFillData['m_account_id'];
                } else {
                    // 例外処理
                    $errors['m_account_id']['required'] = __('validation.required', ['attribute' => '企業アカウントID']);
                }

                if (count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                }

                // 保存
                $new->save();

                // 複数のモデルを更新する場合は、続けて記述する
                // 必要に応じてprivateメソッドとして切り出してもよいが、トランザクション内であるためトランザクションのネストに注意すること

                // 複数のモデルを更新したとしても、返却するのは、その処理の主体となるモデル
                return $new;
            });

            // トランザクション後もしくは別トランザクションで処理を行う場合は、ここに記述する

        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new->m_warehouses_id;
    }
}
