<?php

namespace App\Http\Requests\Gfh1207;

use Illuminate\Foundation\Http\FormRequest;

class CcCustomerListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator)
    {
        $validator->setAttributeNames($this->attributes());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reserve10'        => ['nullable', 'string', 'max:100' ],
            'fax'              => ['nullable', 'max:13', 'regex:/^[a-zA-Z0-9\-]*$/' ],
        ];
    }

    public function attributes()
    {
        return [
            'reserve10'        => 'Web会員番号',
        ];
    }
}
