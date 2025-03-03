<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Base\OperationAuthorityModel;
use App\Modules\Master\Base\GetOperationAuthoritiesInterface;
use App\Services\EsmSessionManager;
use Exception;

class GetOperationAuthorities implements GetOperationAuthoritiesInterface
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
            $query = OperationAuthorityModel::where('delete_flg', '0')
                ->where('m_account_id', $this->esmSessionManager->getAccountId());

            return $query->pluck('m_operation_authority_id', 'm_operation_authority_name')->toArray();
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
