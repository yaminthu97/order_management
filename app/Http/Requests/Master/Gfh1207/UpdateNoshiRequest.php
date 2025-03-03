<?php

namespace App\Http\Requests\Master\Gfh1207;

use App\Http\Requests\Master\Base\UpdateNoshiRequest as BaseUpdateNoshiRequest;
use App\Services\EsmSessionManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Lang;

/**
 * 熨斗マスタ用フォームリクエスト
 */
class UpdateNoshiRequest extends BaseUpdateNoshiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'm_noshi_id' => ['nullable'],
            'noshi_type' => ['required', 'string', 'max:256'],
            'delete_flg' => ['required', Rule::in( array_column(\App\Enums\DeleteFlg::cases(), 'value' ))],
            'attachment_item_group_id' => [ 
                'required', 
                Rule::exists('m_itemname_types','m_itemname_types_id')->where(function ($query) {
                    $query->where([
                        ['m_account_id', $this->service->getAccountId()]
                    ]);
                })
            ],
            'omotegaki' => ['nullable', 'string', 'max:256'],
            'noshi_cd' => ['nullable', 'regex:/^[!-~]+$/', 'max:50'],
            'noshiFormatList.*.m_noshi_format_id' => ['nullable'],
            'noshiFormatList.*.noshi_format_name' => ['required_with:noshiFormatList.*.m_noshi_format_id', 'nullable', 'string', 'max:256'],
            'noshiFormatList.*.delete_flg' => ['required_with::noshiFormatList.*.noshi_format_name', 'nullable', Rule::in( array_column(\App\Enums\DeleteFlg::cases(), 'value' ))],
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages()
    {
        // 熨斗種類名は熨斗種類IDが存在する場合必須にするが、熨斗種類IDは表示のみの項目なので required_with のメッセージだと不都合があるため、必須メッセージに差し替える
        return [
            'noshiFormatList.*.noshi_format_name.required_with' => Lang::get('validation.required'),
        ];
    }

    /**
     * 項目名
     */
    public function attributes()
    {
        return [
            'noshi_type' => '熨斗タイプ',
            'delete_flg' => '使用区分',
            'attachment_item_group_id' => '種別',
            'omotegaki' => '表書き（初期値）',
            'noshi_cd' => '熨斗コード',
            'noshiFormatList' => '熨斗種類',
            'noshiFormatList.*.m_noshi_format_id' => '熨斗種類ID',
            'noshiFormatList.*.noshi_format_name' => '熨斗種類名',
        ];
    }
}