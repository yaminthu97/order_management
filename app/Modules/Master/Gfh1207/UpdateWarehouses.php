<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Warehouse\Gfh1207\WarehouseModel;
use App\Modules\Master\Base\UpdateWarehousesInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class UpdateWarehouses implements UpdateWarehousesInterface
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

    /**
     * 更新処理
     * @param string|int $id 更新対象のID
     * @param array $fillData 更新データ
     * @param array $exFillData fillableに設定されていないデータ
     * @return Model 更新結果(原則としてEloquentのモデルを返す)
     * @throws \App\Exceptions\ModuleValidationException|DataNotFoundException バリデーションエラー時, データが見つからない場合
     */
    public function execute(string|int $id, array $fillData, array $exFillData): Model
    {

        ModuleStarted::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'));

        try {
            // トランザクション開始
            $warehouse = DB::transaction(function () use ($id, $fillData, $exFillData) {
                $operatorId = $this->esmSessionManager->getOperatorId();
                $warehouse = WarehouseModel::findOrFail($id);

                $errors = [];
                // fillできるデータを設定
                $warehouse->fill($fillData);
                $warehouse->update_operator_id = $operatorId;
                // 保存
                $warehouse->save();
                return $warehouse;
            });

            // トランザクション後もしくは別トランザクションで処理を行う場合は、ここに記述する

        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'), $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => 'サンプル情報', 'id' => $id]), 0, $e);
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$warehouse->toArray()]);
        return $warehouse;
    }
}
