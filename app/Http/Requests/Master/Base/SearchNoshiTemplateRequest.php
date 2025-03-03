<?php

namespace App\Http\Requests\Master\Base;

use App\Http\Requests\Common\CommonRequests; 
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

/*熨斗マスタテンプレート検索*/

class SearchNoshiTemplateRequest extends CommonRequests 
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

            'm_noshi_format_id'             => ['required',
                                            Rule::exists('m_noshi_format', 'm_noshi_format_id')->where(function ($query) {
                                                $query->where('m_account_id', $this->service->getAccountId());
                                            }),
            ],

            //検索で追加
            'm_noshi_id'                    => ['nullable',
                                            Rule::exists('m_noshi', 'm_noshi_id')->where(function ($query) {
                                                $query->where('m_account_id', $this->service->getAccountId());
                                            }),
            ],
            
        ];
    }

    /**
     * 項目名
     */
    public function attributes()
    {
        return [
            'm_noshi_format_id'             => '熨斗種類',
            'm_noshi_detail_id'             => '熨斗テンプレート',
            'm_noshi_id'                    => '熨斗ID', //検索
        ];
    }
}
