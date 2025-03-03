<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Base\OperatorModel;
use App\Modules\Master\Base\GetOperatorsInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetOperators implements GetOperatorsInterface
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
            $query = OperatorModel::where('delete_flg', '0')
            ->where('m_account_id', $this->esmSessionManager->getAccountId());

            return $query->pluck('m_operators_id', 'm_operator_name')->toArray();

        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
