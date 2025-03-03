<?php
namespace App\Modules\Order\Base;

use App\Exceptions\DataNotFoundException;
use App\Models\Order\Gfh1207\OrderHdrModel;
use App\Models\Master\Base\OrderTagModel as OrderTagMasterModel;
use App\Models\Order\Base\OrderTagModel;

use App\Modules\Order\Base\UpdateOrderTagInterface;

use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateOrderTag implements UpdateOrderTagInterface
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

    /**
     * 受注タグ追加/削除
     *
     * @param int $orderHdrId 受注基本ID
     * @param int $orderTagId 受注タグID
     * @param array $params 追加パラメータ
     */
    public function execute(int $orderHdrId, int $orderTagId, array $params)
    {
        try{
            $orderHdr = OrderHdrModel::findOrFail($orderHdrId);
            $orderTagMaster = OrderTagMasterModel::findOrFail($orderTagId);
            // トランザクション開始
            $delivery = DB::transaction(function () use ($orderHdr, $orderTagMaster, $params) {
                try{
                    // $orderHdr と $orderTagMaster が一致し、cancel_operator_id が 0 である orderTag があれば取得
                    $orderTag = OrderTagModel::where('t_order_hdr_id', $orderHdr->t_order_hdr_id)
                        ->where('m_account_id', $this->esmSessionManager->getAccountId())
                        ->where('m_order_tag_id', $orderTagMaster->m_order_tag_id)
                        ->where(function($query){
                            $query->where('cancel_operator_id', 0)
                                ->orWhereNull('cancel_operator_id');
                        })
                        ->first();

                    // $orderTagがないにもかかわらず、$params['cancel_flg'] が設定されている場合はエラー
                    if(!$orderTag && isset($params['cancel_flg'])){
                        throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '受注タグ', 'id' => $orderTagMaster->m_order_tag_id]));
                    }
                    // orderTag がなければ新規作成
                    if(!$orderTag){
                        $orderTag = new OrderTagModel();
                        $orderTag->t_order_hdr_id = $orderHdr->t_order_hdr_id;
                        $orderTag->m_order_tag_id = $orderTagMaster->m_order_tag_id;
                        $orderTag->entry_operator_id = $this->esmSessionManager->getOperatorId();
                        $orderTag->entry_timestamp = Carbon::now();
                        $orderTag->m_account_id = $this->esmSessionManager->getAccountId();
                        $orderTag->cancel_operator_id = 0;
                    }

                    // $params['cancel_flg'] が設定されている場合はキャンセルフラグを設定
                    if(isset($params['cancel_flg'])){
                        $orderTag->cancel_operator_id = $this->esmSessionManager->getOperatorId();
                        $orderTag->cancel_timestamp = Carbon::now();
                    }

                    $orderTag->update_operator_id = $this->esmSessionManager->getOperatorId();
                    $orderTag->update_timestamp = Carbon::now();
                    $orderTag->auto_self_flg = 1;
                    $orderTag->save();
                }catch(ModelNotFoundException $e){
                    throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '受注タグ', 'id' => $orderTagMaster->m_order_tag_id]), 0, $e);
                }

                // $orderTag を返却
                return $orderTag;
            });
        }catch(ModelNotFoundException $e){
            // orderDtl か orderTagMaster が見つからなかった場合
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '受注基本情報', 'id' => $orderHdrId]), 0, $e);
        }

        return $delivery;
    }
}
