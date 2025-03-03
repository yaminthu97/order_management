<?php

namespace App\Http\Requests\Ami\Gfh1207;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AmiPageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $mAmiPageId = request()->m_ami_page_id;

        return [
            'm_ami_page_id'             => ['nullable', 'numeric'],
            'page_cd'                   => ['required', 'string', 'max:50', Rule::unique('m_ami_page')->ignore($mAmiPageId, 'm_ami_page_id')],
            'page_title'                => ['required', 'string', 'max:100'],
            'sales_price'               => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'tax_rate'                  => ['required', 'numeric', 'between:0,1'],
            'print_page_title'          => ['nullable', 'string', 'max:100'],
            'sales_start_datetime'      => ['nullable', 'date', 'date_format:Y-m-d H:i'],
            'search_result_display_flg' => ['nullable', 'in:0,1'],
            'page_desc'                 => ['nullable', 'string'],
            'product_img'               => ['nullable', 'file', 'image', 'mimes:jpeg,png', 'max:1024', 'dimensions:max_width=400,max_height=400'],
            'remarks1'                  => ['nullable', 'string', 'max:100'],
            'remarks2'                  => ['nullable', 'string', 'max:100'],
            'remarks3'                  => ['nullable', 'string', 'max:100'],
            'remarks4'                  => ['nullable', 'string', 'max:100'],
            'remarks5'                  => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * 各項目名を書く（書かないと物理名が表示される）
     *
     * @var array
     */
    public function attributes()
    {
        return [
            'product_img'        => '商品画像',
        ];
    }

}
