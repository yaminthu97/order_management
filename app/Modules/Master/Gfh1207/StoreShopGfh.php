<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\ModuleValidationException;
use App\Models\Master\Gfh1207\ShopGfhModel;
use App\Modules\Master\Base\StoreShopGfhInterface;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\DB;

class StoreShopGfh implements StoreShopGfhInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    public function __construct(
        EsmSessionManager $esmSessionManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    //DBへの保存コード
    public function execute(array $fillData)
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData'));

        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($fillData) {
                $operatorId = $this->esmSessionManager->getOperatorId();

                $new = new ShopGfhModel();

                $errors = [];

                // データを設定
                $new->fill($fillData);

                $new->entry_operator_id = $operatorId;
                $new->update_operator_id = $operatorId;

                // Error handling
                if (count($errors) > 0) {
                    throw new ModuleValidationException(__CLASS__, 0, null, $errors);
                }

                // 保存
                $new->save();

                return $new;
            });
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new;
    }
}
