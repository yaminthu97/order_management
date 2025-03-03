<?php

namespace App\Modules\Order\Base;

use App\Models\Order\Base\DeliveryHdrModel;
use App\Models\Order\Base\OrderHdrModel;
use App\Models\Order\Base\OrderTagModel;
use App\Enums\ProgressTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class GetOrderCounts implements GetOrderCountsInterface
{
    /**
     * 追加検索条件
     */
    protected $conditions;
    
    /**
     * 受注情報取得
     *
     * @param int $orderId 受注ID
     */
    public function execute(array $params)
    {
        $progressTypes = collect(ProgressTypeEnum::cases())->map(function ($value) {
            return $value->value;
        })->all();
        $this->conditions = $params;
        
        // 進捗区分（全体）
        $orderCountProgressTotalRows = $this->getOrderCountsByProgressTotal();
        // 進捗区分（当日）
        $orderCountProgressTodayRows = $this->getOrderCountsByProgressToday();
        // 受注タグ
        $orderCountTagsTotalRows = $this->getOrderCountsByOrderTagTotal();

        $resultArray = [];

        $progressCounts = [];
        foreach($progressTypes as $progressType) {
            $totalCount = 0;
            $todayCount = 0;

            if(!empty($orderCountProgressTotalRows[$progressType])) {
                $totalCount = $orderCountProgressTotalRows[$progressType];
            }

            if(!empty($orderCountProgressTodayRows[$progressType])) {
                $todayCount = $orderCountProgressTodayRows[$progressType];
            }

            $progressCounts[$progressType] = ['total' => $totalCount, 'today' => $todayCount];
        }

        // 配送の一覧
        $deliveryCountsRows = $this->getDeliveryCounts();

        $resultArray = [
            'progress_type' => $progressCounts,
            'order_tag' => $orderCountTagsTotalRows,
            'delivery' => $deliveryCountsRows,
        ];

        return $resultArray;
    }

    /**
     * 進捗状況別の受注件数の取得（全体）
     */
    protected function getOrderCountsByProgressTotal()
    {
        $query = OrderHdrModel::query();

        $query->selectRaw('count(t_order_hdr_id) AS count');
        $query->addSelect('progress_type');
        $query = $this->setConditions($query, $this->conditions);
        $query->groupBy('progress_type');

        $query->orderBy('progress_type');

        $dbRow = $query->get();

        $result = [];
        foreach($dbRow as $row) {
            $result[$row->progress_type] = $row->count;
        }

        return $result;
    }

    /**
     * 進捗状況別の受注件数の取得（当日）
     */
    protected function getOrderCountsByProgressToday()
    {
        $today = new Carbon();
        $tomorrow = new Carbon();

        $fromDateTime = $today->format('Y-m-d 00:00:00');
        $toDateTime = $tomorrow->tomorrow()->format('Y-m-d 00:00:00');

        $query = OrderHdrModel::query();

        $query->selectRaw('count(t_order_hdr_id) AS count');
        $query->addSelect('progress_type');
        $query = $this->setConditions($query, $this->conditions);
        $query->where(function ($query) use ($fromDateTime) {
            $query->whereNotNull('progress_update_datetime')
                  ->where('progress_update_datetime', '>=', $fromDateTime)
                  ->orWhere(function ($query) use ($fromDateTime) {
                      $query->whereNull('progress_update_datetime')
                            ->where('entry_timestamp', '>=', $fromDateTime);
                  });
        });
        
        $query->where(function ($query) use ($toDateTime) {
            $query->whereNotNull('progress_update_datetime')
                  ->where('progress_update_datetime', '<', $toDateTime)
                  ->orWhere(function ($query) use ($toDateTime) {
                      $query->whereNull('progress_update_datetime')
                            ->where('entry_timestamp', '<', $toDateTime);
                  });
        });

        $query->groupBy('progress_type');

        $query->orderBy('progress_type');

        $dbRow = $query->get();

        $result = [];
        foreach($dbRow as $row) {
            $result[$row->progress_type] = $row->count;
        }

        return $result;
    }

    /**
     * 受注タグ別の受注件数の取得（全体）
     */
    protected function getOrderCountsByOrderTagTotal()
    {
        $query = OrderTagModel::query();

        $query->selectRaw('count(t_order_hdr_id) AS count');
        $query->selectRaw('m_order_tag_id');
        $query = $this->setConditions($query, $this->conditions);
        $query->where(function ($query) {
            $query->whereNull('cancel_operator_id')
                  ->orWhere('cancel_operator_id', 0);
        });

        $query->groupBy('m_order_tag_id');
        $query->orderBy('m_order_tag_id');

        $dbRow = $query->get();

        $result = [];
        foreach($dbRow as $row) {
            $result[$row->m_order_tag_id] = $row->count;
        }

        return $result;
    }

    /**
     * 発注に関する件数の取得
     */
    protected function getDeliveryCounts()
    {
        $query = DeliveryHdrModel::query();

        $query->whereNull('deli_decision_date');
        $query->whereNull('cancel_operator_id');

        $deliveryRowsBase = json_decode($query->get(), true);

        // 送り状データ出力済み
        $deliveryInvoiceRows = collect($deliveryRowsBase)->filter(function ($value) {
            return !empty($value['invoice_instruct_datetime']);
        })->all();

        // 納品書出力済み
        $deliveryNoteRows = collect($deliveryRowsBase)->filter(function ($value) {
            return !empty($value['deliveryslip_instruct_datetime']);
        })->all();

        // 個別ピッキングリスト出力済み
        $deliveryPickingRows = collect($deliveryRowsBase)->filter(function ($value) {
            return !empty($value['order_pick_instruct_datetime']);
        })->all();

        // 直送発注書出力済み
        $deliveryOrderRows = collect($deliveryRowsBase)->filter(function ($value) {
            return !empty($value['purchase_order_instruct_datetime']);
        })->all();

        return [
            'total' => count($deliveryRowsBase),
            'invoice' => count($deliveryInvoiceRows),
            'note' => count($deliveryNoteRows),
            'picking' => count($deliveryPickingRows),
            'order' => count($deliveryOrderRows),
        ];
    }

    /**
     * 追加検索条件を組み上げる
     */
    public function setConditions($query, $conditions): Builder
    {
        // 追加検索条件を組み上げる
        // m_account_id
        if(isset($conditions['m_account_id'])){
            $query->where('m_account_id', $conditions['m_account_id']);
        }

        return $query;
    }
}
