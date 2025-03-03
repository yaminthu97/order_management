<?php

namespace App\Modules\Master\Gfh1207;

// use App\Enums\DeleteFlg;
// use App\Enums\ItemNameType;
// use App\Exceptions\DataNotFoundException;
use App\Models\Master\Base\NoshiModel;
// use App\Models\Master\Base\NoshiFormatModel;
use App\Modules\Common\CommonModule;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

use Config;
use DB;

/**
 * 熨斗マスタ検索
 */
class SearchNoshiModule extends CommonModule
{
    public function execute(array $conditions)
    {
        // 検索処理
        $query = NoshiModel::query()->with('attachmentItemGroup');
        // 企業アカウントID
        $query->where('m_account_id',$this->getAccountId());
        $query->orderBy('m_noshi_id');
        $limit = $conditions['page_list_count'] ?? Config::get('Common.const.disp_limit_default');
		$page = $conditions['hidden_next_page_no'] ?? 1;
        return $query->paginate($limit, '*', 'hidden_next_page_no', $page);
    }
}
