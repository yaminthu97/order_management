<?php
namespace App\Modules\Order\Base;

use App\Exceptions\DataNotFoundException;

use App\Models\Order\Base\OrderDtlNoshiModel;

use App\Modules\Order\Base\UpdateOrderDtlNoshiInterface;

use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateOrderDtlNoshi implements UpdateOrderDtlNoshiInterface
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
     * 受注明細熨斗更新
     *
     * @param array $datas 更新データ
     */
    public function execute(array $datas)
    {
        return DB::transaction(function () use ($datas) {
            $rv = [];
            foreach($datas as $e){
                $t_order_dtl_noshi_id = $e['t_order_dtl_noshi_id'];
                $orderDtlNoshi = OrderDtlNoshiModel::where('t_order_dtl_noshi_id',$e['t_order_dtl_noshi_id'])
                    ->where('m_account_id', $this->esmSessionManager->getAccountId())->first();
                    
                if($orderDtlNoshi){
                    if(isset($e['shared_flg'])){
                        $orderDtlNoshi->shared_flg = empty($e['shared_flg'])?0:1;
                    }
                    if(isset($e['noshi_file_name'])){
                        $orderDtlNoshi->noshi_file_name = $e['noshi_file_name'];
                    }
                    if(isset($e['output_counter'])){
                        $orderDtlNoshi->output_counter = $e['output_counter'];
                    }
                    if(isset($e['increment_count'])){
                        if(empty($orderDtlNoshi->output_counter)){
                            $orderDtlNoshi->output_counter = 1;
                        } else {
                            $orderDtlNoshi->output_counter += 1;
                        }
                        // ファイル名も設定する
                        $orderDtlNoshi->noshi_file_name = $orderDtlNoshi->t_order_hdr_id.'_'.$orderDtlNoshi->t_order_destination_id.'_'.$orderDtlNoshi->t_order_dtl_id.'_'.$orderDtlNoshi->output_counter.'.pptx';
                    }
                    $orderDtlNoshi->update_operator_id = $this->esmSessionManager->getOperatorId();
                    $orderDtlNoshi->update_timestamp = Carbon::now();
                    $orderDtlNoshi->save();
                    $rv[] = $orderDtlNoshi;
                } else {
                    throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '受注明細熨斗', 'id' => $t_order_dtl_noshi_id]), 0, $e);
                }
            }
            return $rv;
        });
    }
}
