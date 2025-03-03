<?php

namespace App\Http\Requests\Master\Gfh1207;

use App\Http\Requests\Master\Base\NewDeliveryFeesRequest as BaseNewDeliveryFeesRequest;

class NewDeliveryFeesRequest extends BaseNewDeliveryFeesRequest
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
            'm_delivery_fee_id'     =>  ['nullable', 'numeric'],
            'delivery_fee'     =>  ['nullable'],
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'operator_id' => $this->esmSessionManager->getOperatorId(),
        ]);
    }
}
