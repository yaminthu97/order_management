<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Base\ItemnameTypeModel;
use App\Modules\Master\Base\GetItemnameTypeInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetItemnameType implements GetItemnameTypeInterface
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

    public function execute($itemType, $deleteFlag = 0, $orderBy = null)
    {
        try {
            $query = ItemNameTypeModel::where('delete_flg', $deleteFlag)
            ->where('m_account_id', $this->esmSessionManager->getAccountId())
            ->where('m_itemname_type', $itemType);

            if ($orderBy) {
                $query->orderBy('m_itemname_type_sort', 'asc');
            }

            return $query->pluck('m_itemname_types_id', 'm_itemname_type_name')->toArray();

        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
