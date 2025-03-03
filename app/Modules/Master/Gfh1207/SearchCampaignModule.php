<?php

namespace App\Modules\Master\Gfh1207;

use App\Exceptions\DataNotFoundException;
use App\Models\Master\Gfh1207\CampaignModel;
use App\Modules\Common\CommonModule;
use Config;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

use App\Modules\Master\Base\SearchCampaignModuleInterface;


class SearchCampaignModule extends CommonModule implements SearchCampaignModuleInterface
{

    public function execute(array $conditions)
    {
        // 検索処理
        $query = CampaignModel::query();
        
        // 企業アカウントIDを追加
        $query->where('m_account_id', $this->getAccountId());

        // setConditionsで条件を追加
        $query = $this->setConditions($query, $conditions);

        // ソートとページネーション
        $query->orderBy('m_campaign_id');
        $limit = $conditions['page_list_count'] ?? Config::get('Common.const.disp_limit_default');
		$page = $conditions['hidden_next_page_no'] ?? 1;

        return $query->paginate($limit, '*', 'hidden_next_page_no', $page);
    }

    private function setConditions(Builder $query, array $conditions): Builder
    {
        // キャンペーン名の条件追加
        if (!empty($conditions['campaign_name'])) {
            $query->where('campaign_name', 'LIKE', $conditions['campaign_name'] . '%');
        }

        // ページコードの条件追加
        if (!empty($conditions['giving_page_cd'])) {
            $query->where('giving_page_cd', 'LIKE', $conditions['giving_page_cd'] . '%');
        }

        // 開始日の条件追加
        if (!empty($conditions['from_date'])) {
            $query->where('from_date', '>=', $conditions['from_date']);
        }

        // 終了日の条件追加
        if (!empty($conditions['to_date'])) {
            $query->where('to_date', '<=', $conditions['to_date']);
        }

        return $query;
    }
}