<?php

namespace App\Modules\Master\Base;

use App\Models\Master\Base\ReportTemplateModel;
use App\Modules\Common\CommonModule;

use Config;
use DB;

/**
 * 帳票テンプレートマスタ取得
 */
class FindReportTemplates extends CommonModule implements FindReportTemplatesInterface
{
    public function execute( $id ){
        $query = ReportTemplateModel::query()
        ->where('m_account_id', $this->getAccountId())
        ->where('m_report_template_id', $id);
        return $query->first();
    }
}
