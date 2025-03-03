<?php

namespace App\Modules\Ami\Gfh1207;

use App\Models\Master\Base\NoshiFormatModel;
use App\Modules\Ami\Base\GetNoshiFormatInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetNoshiFormat implements GetNoshiFormatInterface
{
    /**
    * ESMセッション管理クラス
    */
    protected $esmSessionManager;
    public const DELETE_FLG = 0;

    public function __construct(
        EsmSessionManager $esmSessionManager,
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute()
    {
        try {
            $accountId = $this->esmSessionManager->getAccountId();
            $deleteFlg = self::DELETE_FLG;

            $query = NoshiFormatModel::query()
                    ->join('m_noshi', 'm_noshi_format.m_noshi_id', '=', 'm_noshi.m_noshi_id')
                    ->where('m_noshi_format.m_account_id', $accountId)
                    ->where('m_noshi.m_account_id', $accountId)
                    ->where('m_noshi_format.delete_flg', $deleteFlg)
                    ->where('m_noshi.delete_flg', $deleteFlg)
                    ->select(
                        'm_noshi_format.m_noshi_id',
                        'm_noshi_format.m_noshi_format_id',
                        'm_noshi_format.noshi_format_name',
                        'm_noshi.noshi_type'
                    )
                    ->get()
                    ->groupBy('m_noshi_id');

            return $query;

        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }

}
