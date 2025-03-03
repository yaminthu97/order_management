<?php

namespace App\Http\Requests\Master\Base;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNoshiNamingPatternRequest extends FormRequest
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
            'delete_flg'=>['required',Rule::in(array_column(\App\Enums\DeleteFlg::cases(), 'value'))],
            'pattern_name' => ['required', 'string', 'max:256'],
            'pattern_code' => ['nullable', 'string', 'max:100'],
            'm_noshi_naming_pattern_sort' => ['nullable', 'numeric','min:1'],
            'company_name_count' => ['nullable', 'numeric','min:0','max:5'],
            'section_name_count' => ['nullable', 'numeric','min:0','max:5'],
            'title_count' => ['nullable', 'numeric','min:0','max:5'],
            'f_name_count' => ['nullable', 'numeric','min:0','max:5'],
            'name_count' => ['nullable', 'numeric','min:0','max:5'],
            'ruby_count' => ['nullable', 'numeric','min:0','max:5'],
        ];
    }

    /**
     * 項目名
     */
    public function attributes()
    {
        return [
            'pattern_name' => '名入れパターン名',
            'pattern_code' => '名入れパターンコード',
            'm_noshi_naming_pattern_sort' => '並び順',
            'company_name_count' => '会社名',
            'section_name_count' => '部署名',
            'title_count' => '肩書',
            'f_name_count' => '苗字',
            'name_count' => '名前',
            'ruby_count' => 'ルビ',
        ];
    }
}