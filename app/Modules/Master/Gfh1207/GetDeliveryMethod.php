<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Base\DeliveryTypeModel;
use App\Modules\Master\Base\GetDeliveryMethodInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetDeliveryMethod implements GetDeliveryMethodInterface
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
     * To get delivery method
    */
    public function execute()
    {
        try {
            $query = DeliveryTypeModel::where('delete_flg', 0)
            ->where('m_account_id', $this->esmSessionManager->getAccountId())
            ->orderBy('m_delivery_sort')->get()->toArray();

            return $query;

        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
