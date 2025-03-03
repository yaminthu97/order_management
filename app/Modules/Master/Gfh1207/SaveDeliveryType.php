<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Base\DeliveryUniqueSettingSeinoModel;
use App\Models\Master\Base\DeliveryUniqueSettingYamatoModel;
use App\Models\Master\Gfh1207\DeliveryTypeModel;
use App\Modules\Master\Base\SaveDeliveryTypeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SaveDeliveryType implements SaveDeliveryTypeInterface
{
    //DBへの保存コード
    public function execute(array $fillData, array $exFillData): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData', 'exFillData'));

        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($fillData, $exFillData) {

                // Save for DeliveryTypeModel
                $newDeliveryType = new DeliveryTypeModel();
                $errors = [];
                $newDeliveryType->fill($fillData);// fillできるデータを設定
                // fillできないデータを設定
                if (isset($exFillData['m_account_id'])) {
                    $newDeliveryType->m_account_id = $exFillData['m_account_id'];
                } else {
                    // 例外処理
                    $errors['m_account_id']['required'] = __('validation.required', ['attribute' => '企業アカウントID']);
                }
                $newDeliveryType->entry_operator_id = $exFillData['m_operator_id'];
                // バリデーションに違反する場合は例外を投げる
                if (count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                }
                // 保存 DeliveryTypeModel
                $newDeliveryType->save();

                // Save for DeliveryUniqueSettingSeinoModel
                $newDeliverySeino = new DeliveryUniqueSettingSeinoModel();
                $newDeliverySeino->fill($fillData);  // Adjust as per data needed
                $newDeliverySeino->m_delivery_types_id = $newDeliveryType->m_delivery_types_id;  // Linking with the above model

                // fillできないデータを設定
                if (isset($exFillData['m_account_id'])) {
                    $newDeliverySeino->m_account_id = $exFillData['m_account_id'];
                } else {
                    // 例外処理
                    $errors['m_account_id']['required'] = __('validation.required', ['attribute' => '企業アカウントID']);
                }
                $newDeliverySeino->entry_operator_id = $exFillData['m_operator_id'];
                // バリデーションに違反する場合は例外を投げる
                if (count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                }
                $newDeliverySeino->save();  // Save DeliveryUniqueSettingSeinoModel

                // Save for another model (example)
                $newDeliveryYamato = new DeliveryUniqueSettingYamatoModel();
                $newDeliveryYamato->fill($fillData);  // Adjust as per data needed
                $newDeliveryYamato->m_delivery_types_id = $newDeliveryType->m_delivery_types_id;
                // fillできないデータを設定
                if (isset($exFillData['m_account_id'])) {
                    $newDeliveryYamato->m_account_id = $exFillData['m_account_id'];
                } else {
                    // 例外処理
                    $errors['m_account_id']['required'] = __('validation.required', ['attribute' => '企業アカウントID']);
                }
                $newDeliveryYamato->entry_operator_id = $exFillData['m_operator_id'];
                // バリデーションに違反する場合は例外を投げる
                if (count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                }
                $newDeliveryYamato->save();  // Save DeliveryUniqueSettingYamatoModel

                return $newDeliveryType;
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
