<?php
namespace App\Modules\Customer\Base;

use App\Models\Order\Base\DestinationModel;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Customer\Base\SearchDestinationsInterface;
use Illuminate\Pagination\Paginator;

class SearchDestinations implements SearchDestinationsInterface
{
    /**
     * デフォルトの検索条件
     */
    protected $defaultConditions = [
        'with_deleted' => '0',
        'delete_flg' => '0'
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
        'm_destination_id' => 'asc'
    ];

    public function execute(array $conditions=[], array $options=[])
    {
        $query = DestinationModel::query();

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

        // 送付先ID
        if(isset($conditions['m_destination_id'])){
            $query->where('m_destination_id', $conditions['m_destination_id']);
        }

        // 顧客ID
        if(isset($conditions['cust_id'])){
            $query->where('cust_id', $conditions['cust_id']);
        }

        // 電話番号
        if(isset($conditions['destination_tel'])){
            $str = str_replace("-", "", $conditions['destination_tel']);
            // destination_tel_forward_match が設定されている場合は前方一致、それ以外は完全一致
            if(isset($conditions['destination_tel_forward_match']) && $conditions['destination_tel_forward_match'] === '1'){
                $query->where('destination_tel', 'like', $str.'%');
            }else{
                $query->where('destination_tel', $str);
            }
        }

        // 名前
        if(isset($conditions['destination_name'])){
            $str = str_replace([" ", "　"], "", $conditions['destination_name']);
            // destination_name_fuzzy が設定されている場合は部分一致、それ以外は前方一致
            if(isset($conditions['destination_name_fuzzy']) && $conditions['destination_name_fuzzy'] === '1'){
                $query->where('gen_search_name', 'like', '%'.$str.'%');
            }else{
                $query->where('gen_search_name', 'like', $str.'%');
            }
        }

        // フリガナ
        if(isset($conditions['destination_name_kana'])){
            $str = str_replace([" ", "　"], "", $conditions['destination_name_kana']);
            // destination_name_kanji_fuzzy が設定されている場合は部分一致、それ以外は前方一致
            if(isset($conditions['destination_name_kana_fuzzy']) && $conditions['destination_name_kana_fuzzy'] === '1'){
                $query->where('gen_search_name_kana', 'like', '%'.$str.'%');
            }else{
                $query->where('gen_search_name_kana', 'like', $str.'%');
            }
        }

        // 郵便番号(前方一致)
        if(isset($conditions['destination_postal'])){
            $query->where('destination_postal', 'like', $conditions['destination_postal'].'%');
        }

        // 都道府県(完全一致)
        if(isset($conditions['destination_address1'])){
            $query->where('destination_address1', $conditions['destination_address1']);
        }

        // 住所
        if(isset($conditions['destination_address2_forward_match']) && ($conditions['destination_address2_forward_match']) ==1 )
        {
            // あいまい検索する場合
            if (isset($conditions['destination_address2']) && strlen($conditions['destination_address2']) > 0 )
            {
                $address2 = str_replace(['%', '_'], ['\%', '\_'], $conditions['destination_address2']);
                $query->whereRaw("CONCAT(IFNULL(m_destinations.destination_address2,''), IFNULL(m_destinations.destination_address3,''), IFNULL(m_destinations.destination_address4,'')) like ?", '%' .$address2 .'%');
            }
        } else {
            // そうでない場合は前方一致
            if (isset($conditions['destination_address2']) && strlen($conditions['destination_address2']) > 0 )
            {
                $address2 = str_replace(['%', '_'], ['\%', '\_'], $conditions['destination_address2']);
                $query->whereRaw("CONCAT(IFNULL(m_destinations.destination_address2,''), IFNULL(m_destinations.destination_address3,''), IFNULL(m_destinations.destination_address4,'')) like ?", [$address2 . '%']);
            }
        }

        // 論理削除判定。delete_flgが明示的に指定されていない場合は、削除されていないものを取得する
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
        // if(isset($options['with'])){
        //     $query->with($options['with']);
        // }

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
