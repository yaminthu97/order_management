<?php

namespace App\Http\Requests\Ami\Gfh1207;

use App\Http\Requests\Ami\Base\UpdateAttachmentitemRequest as BaseUpdateAttachmentitemRequest;
use App\Http\Requests\Common\CommonRequests; 
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

/*付属品マスタ登録編集*/

class UpdateAttachmentitemRequest extends BaseUpdateAttachmentitemRequest 
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
            'm_account_id'             => [
                                            'nullable',
                                            Rule::exists('m_account_id', 'm_account_id')->where(function ($query) {
                                                $query->where('m_account_id', $this->service->getAccountId());
                                            }),
            ],

            'm_ami_attachment_item_id'             => ['nullable',
                                            Rule::exists('m_ami_attachment_items', 'm_ami_attachment_item_id')->where(function ($query) {
                                                $query->where('m_account_id', $this->service->getAccountId());
                                            }),
            ],

            'category_id'            => ['required' , 'numeric', 
                                            Rule::exists('m_itemname_types','m_itemname_types_id')->where(function ($query) {
                                                $query->where('m_account_id', $this->service->getAccountId())
                                                ->where('m_itemname_type', 12)
                                                ->where('delete_flg', 0);
                                            }),
                                            ],

            'attachment_item_cd'        => [
                'required', 
                'string', 
                'max:20', 
                'regex:/^[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};\'\\:"|,.<>\/?]+$/'
            ],
            'attachment_item_name'      => ['required', 'string', 'max:100'],
            'delete_flg'                => ['required', 'in:0,1'],
            'display_flg'               => ['required', 'in:0,1'],
            'invoice_flg'               => ['required', 'in:0,1'],
            'reserve1'                  => ['nullable', 'string', 'max:100'],
            'reserve2'                  => ['nullable', 'string', 'max:100'],
            'reserve3'                  => ['nullable', 'string', 'max:100'],                             
        ];
    }

    /**
     * 項目名
     */
    public function attributes()
    {
        return [
            'category_id'             => 'カテゴリID',
            'attachment_item_cd'      => '付属品コード',
            'attachment_item_name'    => '付属品名称',
            'delete_flg'              => '使用区分',
            'display_flg'             => '受注画面表示',
            'invoice_flg'             => '請求書記載',
            'reserve1'                => '自由項目1',
            'reserve2'                => '自由項目2',
            'reserve3'                => '自由項目3',
        ];
    }
}
