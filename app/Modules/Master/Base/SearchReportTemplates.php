<?php
namespace App\Modules\Master\Base;

use App\Models\Master\Base\ReportTemplateModel;
use App\Services\EsmSessionManager;
use Illuminate\Database\Eloquent\Builder;

class SearchReportTemplates implements SearchReportTemplatesInterface
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
        'm_report_template_id' => 'asc'
    ];

    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    public function __construct(EsmSessionManager $esmSessionManager)
    {
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute(array $conditions=[], array $options=[])
    {
        $query = ReportTemplateModel::query();

        $query = $this->setConditions($query, array_merge($this->defaultConditions, $conditions));

        // 補足情報から追加で検索条件を設定する
        $query = $this->setOptions($query, array_merge($this->defaultOptions, $options));

        return $query->get();
    }


    /**
     * 検索条件を組み上げる
     */
    public function setConditions($query, $conditions): Builder
    {
        if(!isset($conditions['m_account_id'])) {
            // 企業アカウントIDが指定されていない場合は、エラー
            throw new InvalidArgumentException('企業アカウントIDが指定されていません');
        }

        // m_account_id
        $query->where('m_account_id', $conditions['m_account_id']);

        // m_report_template_id
        if(isset($conditions['m_report_template_id'])){
            if( is_array( $conditions['m_report_template_id'] ) ){
                $query->whereIn('m_report_template_id', $conditions['m_report_template_id'] );
            }else{
                $query->where('m_report_template_id', $conditions['m_report_template_id'] );
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
