<?php

namespace App\Http\Requests\Master\Gfh1207;

use App\Http\Requests\Common\CommonRequests; 
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;
use App\Services\EsmSessionManager;

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

/*キャンペーン登録編集*/

class UpdateCampaignRequest extends CommonRequests 
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

        //ルールの名称
        return [
            //
            'm_account_id'             => [
                                            'nullable',
                                            Rule::exists('m_account_id', 'm_account_id')->where(function ($query) {
                                                $query->where('m_account_id', $this->service->getAccountId());
                                            }),
            ],

            'm_campaign_id'             => ['nullable',
                                            Rule::exists('m_campaign', 'm_campaign_id')->where(function ($query) {
                                                $query->where('m_account_id', $this->service->getAccountId());
                                            }),
            ],
            'campaign_name'             => ['required', 'string', 'max:256'],
            'from_date'                 => ['required', 'date',],
            'to_date'                   => ['required', 'date', 'after_or_equal:from_date'],
            'giving_condition_amount'   => ['required', 'numeric'],
            'giving_condition_every'    => ['required', 'in:0,1'],
            'delete_flg'                => ['required', 'in:0,1'],
            'giving_page_cd'            => ['required', 
                                            Rule::exists('m_ami_page','page_cd')->where(function ($query) {
                                                $query->where([
                                                    ['m_account_id', $this->service->getAccountId()]
                                                ]);
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
            'campaign_name'             => 'キャンペーン名',
            'from_date'                 => 'キャンペーン期間FROM',
            'to_date'                   => 'キャンペーン期間TO',
            'giving_condition_amount'   => 'キャンペーン金額',
            'giving_condition_every'    => '金額ごとに追加',
            'delete_flg'                => '使用区分',
            'giving_page_cd'            => '商品コード',
        ];
    }
}
