<?php
namespace App\Modules\Ami\Base;

use App\Models\Ami\Base\AmiAttachmentItemModel;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Ami\Base\SearchAmiAttachmentInterface;
use Illuminate\Pagination\Paginator;

class SearchAmiAttachment implements SearchAmiAttachmentInterface
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
        'm_ami_attachment_item_id' => 'asc'
    ];

    public function execute(array $conditions=[], array $options=[])
    {
        $query = AmiAttachmentItemModel::query();

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

        // 付属品マスタID
        if(isset($conditions['m_ami_attachment_item_id'])){
            $query->where('m_ami_attachment_item_id', $conditions['m_ami_attachment_item_id']);
        }
        
        // 受注時表示フラグ
        if(isset($conditions['display_flg'])){
            $query->where('display_flg', $conditions['display_flg']);
        }
        
        // 請求書記載フラグ
        if(isset($conditions['invoice_flg'])){
            $query->where('invoice_flg', $conditions['invoice_flg']);
        }
        // 付属品コード
        if(isset($conditions['attachment_item_cd'])){
            if(isset($conditions['attachment_item_cd_strict']) && $conditions['attachment_item_cd_strict'] === true){
                // 付属品コード(完全一致)
                $query->where('attachment_item_cd', '=', $conditions['attachment_item_cd']);
            } else {
                // 付属品コード(前方一致)
                $query->where('attachment_item_cd', 'like', $conditions['attachment_item_cd'].'%');
            }
        }

        // 付属品名(attachment_item_name_forward_match が設定されている場合は部分一致、それ以外は部分一致
        if(isset($conditions['attachment_item_name'])){
            if(isset($conditions['attachment_item_name_forward_match']) && $conditions['attachment_item_name_forward_match'] === '1'){
                $query->where('attachment_item_name', 'like', $conditions['attachment_item_name'].'%');
            }else{
                $query->where('attachment_item_name', 'like', '%'.$conditions['attachment_item_name'].'%');
            }
        }

        // カテゴリID
        if(isset($conditions['category_id'])){
            $query->where('category_id', $conditions['category_id']);
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
