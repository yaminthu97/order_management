<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Base\NoshiFormatModel;
use App\Modules\Master\Base\GetNoshiFormatInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetNoshiFormat implements GetNoshiFormatInterface
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
            $query = NoshiFormatModel::where('delete_flg',0)->where('m_account_id', $this->esmSessionManager->getAccountId());
            $query->orderBy('m_noshi_format_id', 'asc');
            return $query->get()->toArray();
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
