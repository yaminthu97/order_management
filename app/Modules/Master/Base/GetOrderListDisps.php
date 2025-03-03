<?php

namespace App\Modules\Master\Base;

use App\Enums\Esm2SubSys;
use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;
use Illuminate\Support\Facades\Log;

use App\Models\Order\Base\OrderListDispModel;
use App\Models\Order\Base\OrderListColumnModel;

class GetOrderListDisps implements GetOrderListDispsInterface
{
    /**
     * ESM2.0 APIマネージャー
     */
    protected $esm2ApiManager;

    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    /**
     * API接続先
     */
    protected $connectionApiUrl = 'searchOrderListDisps';


    public function __construct(Esm2ApiManager $esm2ApiManager, EsmSessionManager $esmSessionManager)
    {
        $this->esm2ApiManager = $esm2ApiManager;
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute($featureId)
    {
        $columnTable = (new OrderListColumnModel)->getTable();
        $dispTable = (new OrderListDispModel)->getTable();

        // OrderListColumnModel をベースとして OrderListDispModel をleftjoinする
        $query = OrderListColumnModel::query();

        $rowArray = $query->from($columnTable . ' as col')
            // Join
            ->leftJoin($dispTable . ' as disp', function ($join) {
                $join->on('col.m_order_list_column_id', '=', 'disp.m_order_list_column_id')
                     ->where('disp.m_operators_id', '=', $this->esmSessionManager->getOperatorId());
            })
            // Order
            ->orderByRaw('disp.m_order_list_disp_sort IS NULL ASC')
            ->orderBy('disp.m_order_list_disp_sort')
            ->orderBy('col.m_default_disp_sort')
            // select
            ->selectRaw('CASE WHEN disp.m_order_list_disp_id IS NULL THEN 0 ELSE 1 END AS delete_flg')
            ->addSelect([
                'disp.m_order_list_disp_id',
                'col.m_order_list_column_id',
                'col.m_column_disp_name',
                'col.m_column_name',
                'disp.m_order_list_disp_sort',
                'col.m_default_disp_sort',
                'disp.entry_operator_id',
                'disp.entry_timestamp',
                'disp.update_operator_id',
                'disp.update_timestamp',
            ]);

        $resultArray = $rowArray->get()->toArray();

		$searchRows = [];
        foreach ($resultArray as $value) {
            // 非表示データは画面に送らない
            if ($value['delete_flg'] == '0'){
                continue;
            }
            array_push($searchRows, $value);
        }

        if(empty($searchRows)){
            //全て非表示の場合、全て表示する（初期表示）
            foreach ($resultArray as $value) {
                array_push($searchRows, $value);
            }
        }
        return $searchRows;
    }

}
