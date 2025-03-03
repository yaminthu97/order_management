<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Master\Gfh1207\ItemnameTypeModel;
use App\Modules\Master\Base\UpdateItemnameTypeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class UpdateItemnameType implements UpdateItemnameTypeInterface
{
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
            $new = DB::transaction(function () use ($id, $fillData, $exFillData) {
                $itemnameTypeModel = ItemnameTypeModel::findOrFail($id);

                $errors = [];
                // fillできるデータを設定
                $itemnameTypeModel->fill($fillData);

                // 保存
                $itemnameTypeModel->save();

                return $itemnameTypeModel;
            });

        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'), $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '項目名称', 'id' => $id]), 0, $e);
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new;
    }
}
