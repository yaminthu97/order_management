<?php

namespace App\Http\Requests\Master\Gfh1207;

use App\Http\Requests\Master\Base\EditItemnameTypeRequest as BaseEditItemnameTypeRequest;
use Illuminate\Validation\Rule;

class EditItemnameTypeRequest extends BaseEditItemnameTypeRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'm_itemname_types_id'     => [ 'required', 'numeric'], // 項目名称マスタID
            'delete_flg'              => [ 'required', Rule::in( array_column(\App\Enums\DeleteFlg::cases(), 'value' ))], // 使用区分
            'm_itemname_type'         => [ 'required', 'max:100', Rule::in( array_column(\App\Enums\ItemnameType::cases(), 'value' ))], // 項目種類
            'm_itemname_type_code'    => [ 'nullable', 'max:100'], // 項目コード
            'm_itemname_type_name'    => [ 'required', 'max:100'], // 項目名
            'm_itemname_type_sort'    => ['required', 'integer', 'max:9999'], // 並び順
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'operator_id' => $this->esmSessionManager->getOperatorId(),
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'delete_flg'               =>  '使用区分',
            'm_itemname_type'          =>  '項目種類',
            'm_itemname_type_code'     =>  '項目コード',
            'm_itemname_type_name'     =>  '項目名',
            'm_itemname_type_sort'     =>  '並び順',
        ];
    }
}
