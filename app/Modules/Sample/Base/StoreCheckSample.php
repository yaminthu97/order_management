<?php
namespace App\Modules\Sample\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\ModuleValidationException;
use App\Models\Cc\Base\CustModel;

class StoreCheckSample implements StoreCheckSampleInterface
{
    public function execute(array $input): bool
    {
        ModuleStarted::dispatch(__CLASS__, compact('input'));
        $errors = [];
        // バリデーション処理

        // 顧客コードの重複チェック
        if(isset($input['cust_cd'])){
            $exists = CustModel::where('cust_cd', $input['cust_cd'])
                ->exists();
            if ($exists) {
                $errors['cust_cd']['unique'] = __('validation.unique', ['attribute' => '顧客コード']);
            }
        }

        // バリデーションエラーがある場合は例外を投げる
        if (count($errors) > 0) {
            $e = new ModuleValidationException(__CLASS__, 0, null, $errors);
            ModuleFailed::dispatch(__CLASS__, compact('input', 'errors'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, compact('input'));
        return true;
    }
}
