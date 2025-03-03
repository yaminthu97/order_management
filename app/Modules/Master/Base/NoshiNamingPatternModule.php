<?php

namespace App\Modules\Master\Base;

use App\Exceptions\DataNotFoundException;
use App\Models\Master\Base\NoshiNamingPatternModel;
use App\Modules\Common\CommonModule;
use Config;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class NoshiNamingPatternModule extends CommonModule
{
    public function list(array $conditions)
    {
        // 検索処理
        $query = NoshiNamingPatternModel::query();
        // 企業アカウントID
        $query->where('m_account_id',$this->getAccountId());
        $query = $this->setCondition($query, $conditions);
        $query->orderBy('m_noshi_naming_pattern_sort');
        $query->orderBy('m_noshi_naming_pattern_id');
        $limit = $conditions['page_list_count'] ?? Config::get('Common.const.disp_limit_default');
		$page = $conditions['hidden_next_page_no'] ?? 1;
        return $query->paginate($limit, '*', 'hidden_next_page_no', $page);
    }
    private function setCondition(Builder $query, array $conditions): Builder
    {
        // 使用区分
        if (isset($conditions['delete_flg']) && !empty($conditions['delete_flg'])) {
            $query->whereIn('delete_flg', $conditions['delete_flg']);
        }
        // 名入れパターン名
        if (isset($conditions['pattern_name']) && strlen($conditions['pattern_name']) > 0) {
            $query->where('pattern_name', 'like', "{$conditions['pattern_name']}%%");
        }
        // 名入れパターンコード
        if (isset($conditions['pattern_code']) && strlen($conditions['pattern_code']) > 0) {
            $query->where('pattern_code', 'like', "{$conditions['pattern_code']}%%");
        }
        return $query;
    }
    public function getOne($id){
        $query = NoshiNamingPatternModel::query();
        $query->where('m_account_id',$this->getAccountId());
        $query->where('m_noshi_naming_pattern_id',$id);
        return $query->first();
    }
    public function save($id,array $data){
        $accountId = $this->getAccountId();
        $operatorId = $this->getOperatorId();
        if(empty($id)){
            $model = new NoshiNamingPatternModel();
            $model->m_account_id = $accountId;
            $model->entry_operator_id = $operatorId;
            $model->update_operator_id = $operatorId;
        } else {
            $model = NoshiNamingPatternModel::find($id);
            $model->update_operator_id = $operatorId;
        }
        if(empty($model) || $model->m_account_id != $accountId){
            throw new DataNotFoundException(__('messages.error.data_not_found',[
                'data'=>'熨斗名入れパターン',
                'id'=>$id
            ]));
        }
        $model->delete_flg = $data['delete_flg'];
        $model->pattern_name = $data['pattern_name'];
        $model->pattern_code = $data['pattern_code'];
        $model->m_noshi_naming_pattern_sort = empty($data['m_noshi_naming_pattern_sort'])?1:$data['m_noshi_naming_pattern_sort'];
        $model->company_name_count = $data['company_name_count'];
        $model->section_name_count = $data['section_name_count'];
        $model->title_count = $data['title_count'];
        $model->f_name_count = $data['f_name_count'];
        $model->name_count = $data['name_count'];
        $model->ruby_count = $data['ruby_count'];
        $model->save();
    }
}
