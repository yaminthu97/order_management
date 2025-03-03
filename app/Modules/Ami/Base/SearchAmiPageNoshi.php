<?php
namespace App\Modules\Ami\Base;

use App\Models\Ami\Base\AmiEcPageModel;
use App\Models\Ami\Base\AmiPageNoshiModel;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Ami\Base\SearchAmiPageNoshiInterface;

class SearchAmiPageNoshi implements SearchAmiPageNoshiInterface
{
    /**
     * デフォルトの検索条件
     */
    protected $defaultConditions = [
    ];

    /**
     * デフォルトの検索オプション
     */
    protected $defaultOptions = [
    ];

    /**
     * デフォルトのソート条件
     */
    protected $defaultSorts = [
        'm_ami_page_noshi_id' => 'asc'
    ];

    public function execute(array $conditions=[], array $options=[])
    {
        $query = AmiPageNoshiModel::query();

        $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

        // 補足情報から追加で検索条件を設定する
        $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

        // ペジネーション設定
        if(isset($options['should_paginate']) && $options['should_paginate'] === true){
            if(isset($options['limit'])){
                return $query->paginate($options['limit']);
            }else{
                return $query->paginate(config('esm.default_page_size.master'));
            }
        }else{
            return $query->get();
        }
    }


    /**
     * 検索条件を組み上げる
     */
    public function setConditions($query, $conditions): Builder
    {
        // 検索条件を組み上げる

        // m_ami_ec_page_id が渡された場合は AmiEcPageModel を利用し m_ami_page_id に変換する
        if(isset($conditions['m_ami_ec_page_id'])){
            $amiEcPageId = $conditions['m_ami_ec_page_id'];
            $amiPage = AmiEcPageModel::where('m_ami_ec_page_id', $amiEcPageId)->first();
            if($amiPage){
                $conditions['m_ami_page_id'] = $amiPage->m_ami_page_id;
            }
        }

        // 熨斗種類ID
        if(isset($conditions['m_noshi_format_id'])){
            $query->where('m_noshi_format_id', $conditions['m_noshi_format_id']);
        }
        
        // ページマスタ管理ID
        if(isset($conditions['m_ami_page_id'])){
            $query->where('m_ami_page_id', $conditions['m_ami_page_id']);
        }

        // m_account_id
        if(isset($conditions['m_account_id'])){
            $query->where('m_account_id', $conditions['m_account_id']);
        }

        // 熨斗ID
        if(isset($conditions['m_noshi_id'])){
            $query->where('m_noshi_id', $conditions['m_noshi_id']);
        }

        return $query;
    }

    /**
     * 補足情報から追加で検索条件を設定する
     */
    public function setOptions($query, $options): Builder
    {
        // 補足情報から追加で検索条件を設定する

        /**
         * @todo カラム指定
         */
        // if(isset($options['columns'])){
        //     $query->select($options['columns']);
        // }

        /**
         * @todo リレーションのeagerload指定
         * 適宜条件も含める
         */
        if(isset($options['with'])){
            $query->with($options['with']);
        }

        // orderby
        if(isset($options['sorts'])){
            if(is_array($options['sorts'])){
                foreach($options['sorts'] as $column => $direction){
                    $query->orderBy($column, $direction);
                }
            }else{
                $query->orderBy($options['sorts'], 'asc');
            }
        }else{
            foreach($this->defaultSorts as $column => $direction){
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }
}
