<?php

namespace App\Modules\Order\Base;

use App\Enums\Esm2SubSys;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;

class RegisterOrderDrawing implements RegisterOrderDrawingInterface
{
    /**
     * ESM2.0 APIマネージャー
     */
    protected $esm2ApiManager;

    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;
    public function __construct(Esm2ApiManager $esm2ApiManager, EsmSessionManager $esmSessionManager)
    {
        $this->esm2ApiManager = $esm2ApiManager;
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute(array $params)
    {
        // 在庫引当API
        $detailInfo = [
            'detail_number' => $params['detail_info']['detail_number'], // 明細番号
            'item_cd' => $params['detail_info']['item_cd'], // 商品コード
            'vol' => $params['detail_info']['vol'], // 数量
            'cancel_timestamp' => $params['detail_info']['cancel_timestamp'] ?? null, // キャンセル日時 日付フォーマット　"yyyymmddhhmmss"
            'drawing_result' => $params['detail_info']['drawing_result'] ?? 0, // 0:未引当、1:引当済
        ];
        $registerOrderDrawing = [
            'process_type' => $params['process_type'], // 1:仮引当、2:本引当、9:本引当解除
            'm_ecs_id' => $params['m_ecs_id'] ?? null, // ECサイトID
            'ec_order_id' => $params['ec_order_id'] ?? null, // ECサイト受注ID
            'order_id' => $params['order_id'] ?? null, // 受注ID
            'forced_delivery' => $params['forced_delivery'] ?? 0, // 強制出荷 0:通常、1:強制出荷
            'detail_info' => $detailInfo, // 引当明細情報
        ];

        $extendData['m_account_id'] = $this->esmSessionManager->getAccountId();

        $response = $this->esm2ApiManager->executeRegisterApi('registerOrderDrawing', Esm2SubSys::STOCK, $registerOrderDrawing, $extendData);

        return $response;
    }
}
