<?php

namespace App\Modules\Customer\Gfh1207;

use App\Models\Cc\Gfh1207\CustCommunicationModel;
use App\Modules\Customer\Base\FindCustCommunicationInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class FindCustCommunication implements FindCustCommunicationInterface
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

    public function execute(int $id)
    {
        try {
            $query = CustCommunicationModel::query();
            $query->where('m_account_id', $this->esmSessionManager->getAccountId());
            $query->where('t_cust_communication_id', $id);
            return $query->first();
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }

}
