<?php
namespace App\Modules\Sample\Base;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Cc\Base\CustModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\AssignOp\Mod;

class UpdateSample implements UpdateSampleInterface
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
            $sample = DB::transaction(function () use ($id, $fillData, $exFillData) {
                $sample = CustModel::findOrFail($id);

                $errors = [];
                // fillできるデータを設定
                $sample->fill($fillData);

                // fillできないデータを設定
                // if(isset($exFillData['xxxx'])){
                //     $sample->xxxx = $exFillData['xxxx'];
                // }

                // バリデーションに違反する場合は例外を投げる
                // if(someCheck()){
                //     $errors['xxxx']['someCheck'] = 'バリデーションエラー';
                // }
                // if(count($errors) > 0){
                //     // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                //     throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                // }

                // 保存
                $sample->save();

                // 複数のモデルを更新する場合は、続けて記述する
                // 必要に応じてprivateメソッドとして切り出してもよいが、トランザクション内であるためトランザクションのネストに注意すること

                // 複数のモデルを更新したとしても、返却するのは、その処理の主体となるモデル
                return $sample;
            });

            // トランザクション後もしくは別トランザクションで処理を行う場合は、ここに記述する

        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'), $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => 'サンプル情報', 'id' => $id]), 0, $e);
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$sample->toArray()]);
        return $sample;
    }
}
