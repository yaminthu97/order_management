<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Gfh1207\OperatorModel;
use App\Modules\Master\Base\GetOperatorUserTypeInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetOperatorUserType implements GetOperatorUserTypeInterface
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

    public function execute()
    {
        try {
            $operatorId = $this->esmSessionManager->getOperatorId();

            $query = OperatorModel::where('m_operators_id', $operatorId)
            ->where('m_account_id', $this->esmSessionManager->getAccountId())
            ->value('user_type');

            return $query;
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
