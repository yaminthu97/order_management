<?php

namespace App\Modules\Order\Gfh1207;

use App\Models\Master\Base\ReportTemplateModel;
use App\Modules\Order\Base\GetTemplateFileNameInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetTemplateFileName implements GetTemplateFileNameInterface
{
    /**
     * To get template file name
     */
    public function execute(string $fileName, int $accountId)
    {
        try {
            $templateData = ReportTemplateModel::query()
            ->where('report_name', $fileName)
            ->where('m_account_id', $accountId)
            ->select('template_file_name', 'report_type')
            ->get();
            $templateName = json_decode($templateData, true);
            return  $templateName;
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
