<?php

namespace App\Modules\Order\Gfh1207;

use App\Models\Goto\Gfh1207\DepositorNumberModel;
use App\Modules\Order\Base\GetProcessDateTimeInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetProcessDateTime implements GetProcessDateTimeInterface
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
     * To get process datetime
     */
    public function execute()
    {
        try {

            $processDate = DepositorNumberModel::query()
            ->where('entry_operator_id', $this->esmSessionManager->getOperatorId())
            ->orderBy('process_timestamp', 'desc')
            ->limit(30)
            ->get();
            $processDateData = json_decode($processDate, true);

            return  $processDateData;
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
