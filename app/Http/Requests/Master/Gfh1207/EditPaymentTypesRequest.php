<?php

namespace App\Http\Requests\Master\Gfh1207;

use App\Http\Requests\Master\Base\EditPaymentTypesRequest as BaseEditPaymentTypesRequest;
use Illuminate\Validation\Rule;

class EditPaymentTypesRequest extends BaseEditPaymentTypesRequest
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
            'm_payment_types_id'         => ['required'],
            'delete_flg'                 => ['required', Rule::in(array_column(\App\Enums\DeleteFlg::cases(), 'value'))],
            'payment_type'               => ['required', Rule::in(array_column(\App\Enums\PaymentMethodTypeEnum::cases(), 'value'))],
            'm_payment_types_name'       => ['required', 'string', 'max:100'],
            'm_payment_types_code'       => ['nullable', 'string', 'max:100'],
            'delivery_condition'         => ['required', Rule::in(array_column(\App\Modules\Master\Gfh1207\Enums\DeliveryConditionEnum::cases(), 'value'))],
            'settlement_management_url'  => ['nullable', 'string', 'url', 'max:255'],
            'm_payment_types_sort'       => ['required', 'integer', 'max:9999'],
            'payment_fee' => ['nullable', 'numeric', 'max:9999'],
            'atobarai_com_cooperation_type'       => ['nullable', 'integer', Rule::in(array_column(\App\Modules\Master\Gfh1207\Enums\CooperationType::COOPERATE::cases(), 'value'))],
            'atobarai_com_url'       => ['nullable', 'string', 'url', 'max:500'],
            'atobarai_com_acceptance_company_id'       => ['nullable', 'string', 'max:150'],
            'atobarai_com_apiuser_id'       => ['nullable', 'string', 'max:150'],
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'operator_id' => $this->esmSessionManager->getOperatorId(),
        ]);
    }
}
