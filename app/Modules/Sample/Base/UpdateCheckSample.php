<?php
namespace App\Modules\Sample\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Cc\Base\CustModel;
use PhpParser\Node\Expr\AssignOp\Mod;

class UpdateCheckSample implements UpdateCheckSampleInterface
{
    public function execute(string|int $id, array $input): bool
    {
        ModuleStarted::dispatch(__CLASS__, compact('id', 'input'));
        $errors = [];
        // バリデーション処理
        // IDが存在するかチェック
        $sample = CustModel::find($id);
        if (is_null($sample)) {
            $errors['m_cust_id']['exists'] = __('validation.exists', ['attribute' => '顧客']);
        }

        // 顧客コードの重複チェック
        if(isset($input['cust_cd'])){
            $exists = CustModel::where('cust_cd', $input['cust_cd'])
                ->where('m_cust_id', '!=', $id)
                ->exists();
            if ($exists) {
                $errors['cust_cd']['unique'] = __('validation.unique', ['attribute' => '顧客コード']);
            }
        }

        // バリデーションエラーがある場合は例外を投げる
        if (count($errors) > 0) {
            $e = new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
            ModuleFailed::dispatch(__CLASS__, compact('id', 'input', 'errors'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, compact('id', 'input'));
        return true;
    }
}
