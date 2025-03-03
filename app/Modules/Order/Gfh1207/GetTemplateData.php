<?php

namespace App\Modules\Order\Gfh1207;

use App\Models\Master\Base\ReportTemplateModel;
use App\Modules\Order\Base\GetTemplateDataInterface;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetTemplateData implements GetTemplateDataInterface
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
     * To get all template data
     */
    public function execute(array $idData)
    {
        try {
            $templateData = ReportTemplateModel::query()
                    ->where('m_account_id', $this->esmSessionManager->getAccountId())
                    ->whereIn('m_report_template_id', $idData) // Filter by allowed IDs
                    ->orderBy('m_report_template_id')
                    ->get()
                    ->groupBy(function ($item) {
                        return (int) floor($item->m_report_template_id / 100) * 100;
                    })
                    ->toArray();

            return  $templateData;
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
