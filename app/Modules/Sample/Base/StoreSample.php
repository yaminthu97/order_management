<?php
namespace App\Modules\Sample\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Cc\Base\CustModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StoreSample implements StoreSampleInterface
{
    public function execute(array $fillData, array $exFillData): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData', 'exFillData'));

        try{
            // トランザクションを張る
            $new = DB::transaction(function () use ($fillData, $exFillData) {
                $new = new CustModel();

                $errors = [];
                // fillできるデータを設定
                $new->fill($fillData);

                // fillできないデータを設定
                if(isset($exFillData['m_account_id'])){
                    $new->m_account_id = $exFillData['m_account_id'];
                }else{
                    // 例外処理
                    $errors['m_account_id']['required'] = __('validation.required', ['attribute' => '企業アカウントID']);
                }

                // バリデーションに違反する場合は例外を投げる
                // if(someCheck()){
                //     $errors['xxxx']['someCheck'] = 'バリデーションエラー';
                // }
                if(count($errors) > 0){
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

        }catch(\Exception $e){
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData', 'errors'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new;
    }
}
