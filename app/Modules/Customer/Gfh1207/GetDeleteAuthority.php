<?php

namespace App\Modules\Customer\Base;

use App\Models\Master\Base\OperatorModel;
use App\Modules\Customer\Gfh1207\Enums\AuthorityCode;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetDeleteAuthority implements GetDeleteAuthorityInterface
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
        $admin =  AuthorityCode::ADMIN->value;
        try {
            $operatorId = $this->esmSessionManager->getOperatorId();
            $query = OperatorModel::where('m_operators_id', $operatorId)
                      ->where('cc_authority_code', $admin)
                      ->exists();

            return $query;

        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
