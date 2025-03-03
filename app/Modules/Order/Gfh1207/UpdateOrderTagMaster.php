<?php

namespace App\Modules\Order\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Master\Gfh1207\OrderTagModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class UpdateOrderTagMaster implements UpdateOrderTagMasterInterface
{
    public function execute(string|int $id, array $fillData, array $exFillData): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'));

        try {
            // トランザクション開始
            $new = DB::transaction(function () use ($id, $fillData, $exFillData) {
                $orderTagModel = OrderTagModel::findOrFail($id);

                $errors = [];
                // fillできるデータを設定
                $orderTagModel->fill($fillData);

                // 保存
                $orderTagModel->save();

                return $orderTagModel;
            });

        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'), $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '受注タグ情報', 'id' => $id]), 0, $e);
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new;
    }
}
