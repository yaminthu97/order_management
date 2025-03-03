<?php

namespace App\Http\Requests\Master\Base;

use App\Http\Requests\Common\CommonRequests; 
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Master\Base\NoshiDetailModel;

use Illuminate\Http\Request;
use App\Services\EsmSessionManager;
use App\Exceptions\DataNotFoundException;

/**
 * 値チェック
 *
 * 
 * 
 * @category Cc
 * @package Validator
 * 
 * 追加
 */

/*熨斗マスタテンプレート登録編集(詳細登録)*/

class UpdateNoshiTemplateRequest extends CommonRequests 
{

    protected $service;

    public function __construct()
    {
        $this->service = app(EsmSessionManager::class); // サービスコンテナから取得
    }
    
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        // Requestから必要なパラメータを取得
        $m_noshi_detail_id = $this->input('m_noshi_detail_id');
        $m_noshi_id = $this->input('m_noshi_id');
        $m_noshi_naming_pattern_id = $this->input('m_noshi_naming_pattern_id');
        $m_noshi_format_id = $this->input('m_noshi_format_id');

        //ルールの名称
        return [
            'm_account_id'             => [
                                            'nullable',
                                            Rule::exists('m_account_id', 'm_account_id')->where(function ($query) {
                                                $query->where('m_account_id', $this->service->getAccountId());
                                            }),
            ],

            'm_noshi_detail_id'             => ['nullable',
                                            Rule::exists('m_noshi_detail', 'm_noshi_detail_id')->where(function ($query) {
                                                $query->where('m_account_id', $this->service->getAccountId());
                                            }),
            ],


            'm_noshi_naming_pattern_id'      => ['required',
                                            Rule::exists('m_noshi_naming_pattern', 'm_noshi_naming_pattern_id')->where(function ($query) {
                                                $query->where('m_account_id', $this->service->getAccountId());
                                            }),

                                            // 重複チェックを追加
                                            function ($attribute, $value, $fail) use ($m_noshi_id, $m_noshi_detail_id, $m_noshi_format_id) {
                                                $query = NoshiDetailModel::where('m_noshi_id', $m_noshi_id)
                                                ->where('m_noshi_naming_pattern_id', $value)
                                                ->where('m_noshi_format_id', $m_noshi_format_id)
                                                ->where('delete_flg', 0); // 使用中のみを対象
                                
                                                // 更新の場合は、自身のIDを除外
                                                if ($m_noshi_detail_id) {
                                                    $query->where('m_noshi_detail_id', '<>', $m_noshi_detail_id);
                                                }
                                
                                                if ($query->exists()) {
                                                    $fail(__('messages.error.naming_pattern_duplicat',['naming_pattern'=>'名入れパターンの選択']));
                                                }
                                            },
            ],

            'm_noshi_format_id'             => ['nullable',
                                            Rule::exists('m_noshi_format', 'm_noshi_format_id')->where(function ($query) {
                                                $query->where('m_account_id', $this->service->getAccountId());
                                            }),
            ],


            'm_noshi_id'                    => ['nullable',
                                            Rule::exists('m_noshi', 'm_noshi_id')->where(function ($query) {
                                                $query->where('m_account_id', $this->service->getAccountId());
                                            }),
            ],


            'delete_flg'                => ['nullable', 'in:0,1'], //0か1以外

            // ファイルのバリデーションを追加
            'file' => ['nullable', 'file', 'mimes:pptx'],  // 'mimes:pptx'でpptx拡張子のみ許可
            
        ];
    }

    /**
     * 項目名
     */
    public function attributes()
    {
        return [
            'delete_flg'                    => '使用区分',
            'file'                          => 'ファイル',
            'm_noshi_naming_pattern_id'     => '熨斗名入れパターン',
            'm_noshi_format_id'             => '熨斗種類',
            'm_noshi_detail_id'             => '熨斗テンプレート',
            'm_noshi_id'                    => '熨斗ID',
        ];
    }
}
