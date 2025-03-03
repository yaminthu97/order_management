<?php

namespace App\Http\Requests\Master\Gfh1207;

use App\Http\Requests\Master\Base\EditDeliveryFeesRequest as BaseEditDeliveryFeesRequest;

class EditDeliveryFeesRequest extends BaseEditDeliveryFeesRequest
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
            'delivery_fee'     =>  ['required'],
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'operator_id' => $this->esmSessionManager->getOperatorId(),
        ]);
    }
}
