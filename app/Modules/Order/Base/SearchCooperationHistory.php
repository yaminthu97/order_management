<?php
namespace App\Modules\Order\Base;

use App\Models\Order\Base\CooperationHistoryModel;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class SearchCooperationHistory implements SearchCooperationHistoryInterface
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
        'entry_timestamp' => 'desc'
    ];

    /**
     * 連携履歴を検索する
     *
     * @param array $conditions 検索条件
     * @param array $options 検索オプション
     */
    public function execute(array $conditions = [], array $options = [])
    {
        $query = CooperationHistoryModel::query();

        $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

        // 補足情報から追加で検索条件を設定する
        $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

        // ペジネーション設定
        if(isset($options['should_paginate']) && $options['should_paginate'] === true) {
            if(isset($options['limit'])) {
                return $query->paginate($options['limit']);
            } else {
                return $query->paginate(config('esm.default_page_size.order'));
            }
        } else {
            return $query->get();
        }

    }

    /**
     * 検索条件を組み上げる
     */
    public function setConditions($query, $conditions): Builder
    {
        // 検索条件を組み上げる
        // 企業アカウントID
        if(isset($conditions['m_account_id'])) {
            $query->where('m_account_id', $conditions['m_account_id']);
        } else {
            // 企業アカウントIDが指定されていない場合は、エラー
            throw new InvalidArgumentException('企業アカウントIDが指定されていません');
        }
        
        // 受注ID t_order_hdr_id
        if(isset($conditions['t_order_hdr_id'])) {
            $query->where('t_order_hdr_id', $conditions['t_order_hdr_id']);
        }

        // 論理削除判定。delete_flgが明示的に指定されていない場合は、削除されていないものを取得する
        if(isset($conditions['with_deleted'])) {
            if($conditions['with_deleted'] === '1') {
                // delete_flgが明示的に1の場合は、削除済みのみ取得する
                if(isset($conditions['delete_flg']) && $conditions['delete_flg'] === '1') {
                    $query->where('delete_flg', '1');
                } else {
                    // そうでなければ、削除済みと削除されていないもの両方を取得する
                }
            } else {
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
        if(isset($options['with'])){
            $query->with($options['with']);
        }

        // orderby
        if(isset($options['sorts'])) {
            if(is_array($options['sorts'])) {
                foreach($options['sorts'] as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            } else {
                $query->orderBy($options['sorts'], 'asc');
            }
        } else {
            foreach($this->defaultSorts as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }
}
