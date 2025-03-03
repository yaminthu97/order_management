<?php

namespace App\Http\Controllers\Master;

use App\Http\Requests\Master\Base\ShopGfhRequest;
use App\Modules\Master\Base\GetShopGfhInterface;
use App\Modules\Master\Base\StoreShopGfhInterface;
use App\Modules\Master\Base\UpdateShopGfhInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * 基本設定マスタ（GFH）登録・変更
 */
class ShopGfhController
{
    /**
     * 変更画面表示
     *
     * @param int 基本設定マスタ管理
     * @return type
     */
    public function edit(
        GetShopGfhInterface $getShopGfh
    ) {
        try {
            // 基本設定からデータを取得
            $editRow = $getShopGfh->execute();

            // 基本設定レコードが空場合
            if (is_null($editRow)) {
                $editRow  = [];
            }
        } catch (Exception $e) {
            $editRow = [];
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
        }

        return account_view('master.shopGfh.base.edit', compact('editRow'));
    }

    /**
     * 登録・変更
     *
     * @param Request $request 画面情報
     * @return type
     */
    public function postEdit(
        ShopGfhRequest $request,
        StoreShopGfhInterface $storeShopGfh,
        UpdateShopGfhInterface $updateShopGfh,
        GetShopGfhInterface $getShopGfh,
    ) {
        $input = $request->validated();

        try {
            // 基本設定からデータを取得
            $shopGfhData = $getShopGfh->execute();

            // レコードが空の場合にレコードを作成し、レコードが存在する場合にレコードを編集
            if (!empty($shopGfhData)) {
                // 基本設定ID取得
                $shop_id = $shopGfhData['m_shop_gfh_id'];
                // データ更新
                $updateShopGfh->execute($shop_id, $input);

                $flashData = [
                    'editRow'       => $input,
                    'messages.info' => ['message' => __('messages.info.update_completed', ['data' => '基本設定'])]
                ];
            } else {
                // データ作成
                $storeShopGfh = $storeShopGfh->execute($input);

                $flashData = [
                    'messages.info' => ['message' => __('messages.info.create_completed', ['data' => '基本設定'])]
                ];
            }
            return redirect()->route('master.shop_gfh.edit')->with($flashData);
        } catch (Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            $this->checkErrorException('connectionError');
            return redirect()->route('master.shop_gfh.edit');
        }
    }

    /**
     * show error message for connection error
     */
    public function checkErrorException($results = '', $message = '')
    {
        if ($results === 'connectionError') {
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
        } elseif ($message != '') {
            session()->flash('messages.error', ['message' => __($message)]);
        }
    }
}
