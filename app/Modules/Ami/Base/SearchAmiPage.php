<?php
namespace App\Modules\Ami\Base;

use App\Models\Ami\Base\AmiEcPageModel;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Ami\Base\SearchAmiPageInterface;
use Illuminate\Pagination\Paginator;

class SearchAmiPage implements SearchAmiPageInterface
{
    /**
     * デフォルトの検索条件
     */
    protected $defaultConditions = [
        //'delete_flg' => '0'
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
        'ec_page_cd' => 'asc'
    ];

    public function execute(array $conditions=[], array $options=[])
    {
        $query = AmiEcPageModel::query();

        $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

        // 補足情報から追加で検索条件を設定する
        $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

        // ペジネーション設定
        if(isset($options['should_paginate']) && $options['should_paginate'] === true){
            if (isset($options['page'])) {
                Paginator::currentPageResolver(function () use ($options) {
                    return $options['page'];
                });
            }
            $options['limit'] = $options['limit'] ?? config('esm.default_page_size.master');
            $results = $query->paginate($options['limit']);
            return $results;
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

        // m_ami_ec_page_cd
        if(isset($conditions['m_ami_ec_page_id'])){
            $query->where('m_ami_ec_page_id', $conditions['m_ami_ec_page_id']);
        }

        // m_ami_page_id
        if(isset($conditions['m_ami_page_id'])){
            $query->where('m_ami_page_id', $conditions['m_ami_page_id']);
        }

        // m_ecs_id(必須)
        if(isset($conditions['m_ecs_id'])){
            $query->where('m_ecs_id', $conditions['m_ecs_id']);
        }

        // m_ec_type
        if(isset($conditions['m_ec_type'])){
            $query->where('m_ec_type', $conditions['m_ec_type']);
        }

        
        // 販売コード
        if(isset($conditions['ec_page_cd'])){
            if(isset($conditions['ec_page_cd_strict']) && $conditions['ec_page_cd_strict'] === true){
                // 販売コード(完全一致)
                $query->where('ec_page_cd', '=', $conditions['ec_page_cd']);
            } else {
                // 販売コード(前方一致)
                $query->where('ec_page_cd', 'like', $conditions['ec_page_cd'].'%');
            }
        }

        // 販売名(page_title_forward_match が設定されている場合は部分一致、それ以外は部分一致
        if(isset($conditions['ec_page_title'])){
            if(isset($conditions['ec_page_title_forward_match']) && $conditions['ec_page_title_forward_match'] === '1'){
                $query->where('ec_page_title', 'like', $conditions['ec_page_title'].'%');
            }else{
                $query->where('ec_page_title', 'like', '%'.$conditions['ec_page_title'].'%');
            }
        }

        // 説明文(部分一致)
        if(isset($conditions['page_desc'])){
            $query->where('page_desc', 'like', '%'.$conditions['page_desc'].'%');
        }
        
        // 販売価格 FROM
        if(isset($conditions['sales_price_from'])){
            $query->where('sales_price', '>=', $conditions['sales_price_from']);
        }

        // 販売価格 TO
        if(isset($conditions['sales_price_to'])){
            $query->where('sales_price', '<=', $conditions['sales_price_to']);
        }

        // 論理削除判定。delete_flgが明示的に指定されていない場合は、削除されていないものを取得する
        /*
        if(isset($conditions['with_deleted'])){
            if($conditions['with_deleted'] === '1'){
                // delete_flgが明示的に1の場合は、削除済みのみ取得する
                if(isset($conditions['delete_flg']) && $conditions['delete_flg'] === '1'){
                    $query->where('delete_flg', '1');
                }else{
                    // そうでなければ、削除済みと削除されていないもの両方を取得する
                }
            }else{
                $query->where('delete_flg', '0');
            }
        }
        */

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
