<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Base\DeliveryUniqueSettingSeinoModel;
use App\Modules\Master\Base\SaveDeliveryUniqueSettingSeinoInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SaveDeliveryUniqueSettingSeino implements SaveDeliveryUniqueSettingSeinoInterface
{
    //DBへの保存コード
    public function execute(array $fillData, array $exFillData): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData', 'exFillData'));

        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($fillData, $exFillData) {

                if (isset($fillData['m_delivery_unique_setting_seino_id'])) {
                    $new = DeliveryUniqueSettingSeinoModel::findOrFail($fillData['m_delivery_unique_setting_seino_id']);
                } else {
                    $new = new DeliveryUniqueSettingSeinoModel();
                }
                $errors = [];
                // fillできるデータを設定
                $new->fill($fillData);

                // fillできないデータを設定
                if (isset($exFillData['m_account_id'])) {
                    $new->m_account_id = $exFillData['m_account_id'];
                } else {
                    // 例外処理
                    $errors['m_account_id']['required'] = __('validation.required', ['attribute' => '企業アカウントID']);
                }
                $new->entry_operator_id = $exFillData['m_operator_id'];
                $new->update_operator_id = $exFillData['m_operator_id'];

                if (count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                }

                // 保存
                $new->save();

                return $new;
            });

            // トランザクション後もしくは別トランザクションで処理を行う場合は、ここに記述する

        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new;
    }
}
