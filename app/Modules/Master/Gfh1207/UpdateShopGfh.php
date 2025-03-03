<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\ModuleValidationException;
use App\Models\Master\Gfh1207\ShopGfhModel;
use App\Modules\Master\Base\UpdateShopGfhInterface;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\DB;

class UpdateShopGfh implements UpdateShopGfhInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;


    public function __construct(
        EsmSessionManager $esmSessionManager,
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    //DBへの保存コード
    public function execute(int $id, array $fillData)
    {
        ModuleStarted::dispatch(__CLASS__, compact('id', 'fillData'));

        try {
            // トランザクションを張る
            $shopGfh = DB::transaction(function () use ($id, $fillData) {
                $operatorId = $this->esmSessionManager->getOperatorId();

                $shopGfh = ShopGfhModel::findOrFail($id);

                $errors = [];

                // データを設定
                $shopGfh->fill($fillData);

                $shopGfh->update_operator_id = $operatorId;

                // Error handling
                if (count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new ModuleValidationException(__CLASS__, 0, null, $errors);
                }

                // 保存
                $shopGfh->save();
                return $shopGfh;
            });
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$shopGfh->toArray()]);
        return $shopGfh;
    }
}
