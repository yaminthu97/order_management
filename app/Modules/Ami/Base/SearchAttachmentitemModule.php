<?php

namespace App\Modules\Ami\Base;

use App\Exceptions\DataNotFoundException;
use App\Models\Ami\Gfh1207\AttachmentitemModel;
use App\Modules\Common\CommonModule;
use Config;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

use App\Modules\Ami\Base\SearchAttachmentitemModuleInterface;


class SearchAttachmentitemModule extends CommonModule implements SearchAttachmentitemModuleInterface
{

    public function execute(array $conditions)
    {
        // 検索処理
        $query = AttachmentitemModel::query();
        
        // 企業アカウントIDを追加
        $query->where('m_account_id', $this->getAccountId());

        // setConditionsで条件を追加
        $query = $this->setConditions($query, $conditions);

        // ページネーション
        $query->orderBy('m_ami_attachment_item_id');
        $limit = $conditions['page_list_count'] ?? Config::get('Common.const.disp_limit_default');
		$page = $conditions['hidden_next_page_no'] ?? 1;

        return $query->paginate($limit, '*', 'hidden_next_page_no', $page);
    }

    private function setConditions(Builder $query, array $conditions): Builder
    {

        // 検索条件を組み上げる
        // 付属品マスタID
        if(isset($conditions['m_ami_attachment_item_id'])){
            $query->where('m_ami_attachment_item_id', $conditions['m_ami_attachment_item_id']);
        }

        // 付属品コード(前方一致)
        if(isset($conditions['attachment_item_cd'])){
            $query->where('attachment_item_cd', 'like', $conditions['attachment_item_cd'].'%');
        }

        // 付属品名称(前方一致)
        if(isset($conditions['attachment_item_name'])){
            $query->where('attachment_item_name', 'like', $conditions['attachment_item_name'].'%');
        }

        // カテゴリID
        if(isset($conditions['category_id'])){
            $query->where('category_id', $conditions['category_id']);
        }

        // 使用区分
        if (isset($conditions['delete_flg']) && !empty($conditions['delete_flg'])) {
            $query->whereIn('delete_flg', $conditions['delete_flg']);
        }

        // 受注表示区分
        if (isset($conditions['display_flg']) && !empty($conditions['display_flg'])) {
            $query->whereIn('display_flg', $conditions['display_flg']);
        }

        // 請求書記載
        if (isset($conditions['invoice_flg']) && !empty($conditions['invoice_flg'])) {
            $query->whereIn('invoice_flg', $conditions['invoice_flg']);
        }

        // 自由項目1(前方一致)
        if(isset($conditions['reserve1'])){
            $query->where('reserve1', 'like', $conditions['reserve1'].'%');
        }

        // 自由項目2(前方一致)
        if(isset($conditions['reserve2'])){
            $query->where('reserve2', 'like', $conditions['reserve2'].'%');
        }

        // 自由項目3(前方一致)
        if(isset($conditions['reserve3'])){
            $query->where('reserve3', 'like', $conditions['reserve3'].'%');
        }

        return $query;
    }
}