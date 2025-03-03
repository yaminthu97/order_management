<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Base\NoshiModel;
use App\Modules\Master\Base\GetNoshiInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetNoshi implements GetNoshiInterface
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
            $query = NoshiModel::where('delete_flg',0)->where('m_account_id', $this->esmSessionManager->getAccountId());
            $query->orderBy('m_noshi_id', 'asc');
            return $query->get()->toArray();
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
