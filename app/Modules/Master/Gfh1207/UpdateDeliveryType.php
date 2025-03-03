<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Base\DeliveryUniqueSettingSeinoModel;
use App\Models\Master\Base\DeliveryUniqueSettingYamatoModel;
use App\Models\Master\Gfh1207\DeliveryTypeModel;
use App\Modules\Master\Base\UpdateDeliveryTypeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UpdateDeliveryType implements UpdateDeliveryTypeInterface
{
    //DBへの保存コード
    public function execute(array $fillData, array $exFillData): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData', 'exFillData'));

        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($fillData, $exFillData) {
                // for DeliveryTypeModel
                $deliveryTypeModel = DeliveryTypeModel::findOrFail($fillData['m_delivery_types_id']);
                $errors = [];
                // fillできるデータを設定
                $deliveryTypeModel->fill($fillData);
                // fillできないデータを設定
                if (isset($exFillData['m_account_id'])) {
                    $deliveryTypeModel->m_account_id = $exFillData['m_account_id'];
                } else {
                    // 例外処理
                    $errors['m_account_id']['required'] = __('validation.required', ['attribute' => '企業アカウントID']);
                }
                $deliveryTypeModel->update_timestamp = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
                $deliveryTypeModel->update_operator_id = $exFillData['m_operator_id'];
                if (count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                }
                // 保存 DeliveryTypeModel
                $deliveryTypeModel->save();

                // for DeliveryUniqueSettingSeinoModel
                $deliverySeinoModel = DeliveryUniqueSettingSeinoModel::firstOrNew(['m_delivery_unique_setting_seino_id' => $fillData['m_delivery_unique_setting_seino_id']]);
                // fillできるデータを設定
                $deliverySeinoModel->fill($fillData);
                // fillできないデータを設定
                if (isset($exFillData['m_account_id'])) {
                    $deliverySeinoModel->m_account_id = $exFillData['m_account_id'];
                } else {
                    // 例外処理
                    $errors['m_account_id']['required'] = __('validation.required', ['attribute' => '企業アカウントID']);
                }
                $deliverySeinoModel->update_timestamp = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
                $deliverySeinoModel->update_operator_id = $exFillData['m_operator_id'];
                if (count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                }
                // 保存 DeliveryTypeModel
                $deliverySeinoModel->save();

                // for DeliveryUniqueSettingYamatoModel
                $deliveryYamatoModel = DeliveryUniqueSettingYamatoModel::firstOrNew(['m_delivery_unique_setting_yamato_id' => $fillData['m_delivery_unique_setting_yamato_id']]);
                // fillできるデータを設定
                $deliveryYamatoModel->fill($fillData);
                // fillできないデータを設定
                if (isset($exFillData['m_account_id'])) {
                    $deliveryYamatoModel->m_account_id = $exFillData['m_account_id'];
                } else {
                    // 例外処理
                    $errors['m_account_id']['required'] = __('validation.required', ['attribute' => '企業アカウントID']);
                }
                $deliveryYamatoModel->update_timestamp = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
                $deliveryYamatoModel->update_operator_id = $exFillData['m_operator_id'];
                if (count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                }
                // 保存 DeliveryTypeModel
                $deliveryYamatoModel->save();

                return $deliveryTypeModel;
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
