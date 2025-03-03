<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Base\NoshiNamingPatternModel;
use App\Modules\Master\Base\GetNoshiNamingPatternInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetNoshiNamingPattern implements GetNoshiNamingPatternInterface
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
            $query = NoshiNamingPatternModel::where('delete_flg',0)->where('m_account_id', $this->esmSessionManager->getAccountId());
            $query->orderBy('m_noshi_naming_pattern_sort', 'asc');
            $query->orderBy('m_noshi_naming_pattern_id', 'asc');
            return $query->get()->toArray();
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
