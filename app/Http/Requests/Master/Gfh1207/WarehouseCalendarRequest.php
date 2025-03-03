<?php

namespace App\Http\Requests\Master\Gfh1207;

use App\Http\Requests\Master\Base\WarehouseCalendarRequest as BaseWarehouseCalendarRequest;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class WarehouseCalendarRequest extends BaseWarehouseCalendarRequest
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
            'holidays'     =>  ['nullable'],
            'year'         =>  ['nullable'],
        ];
    }

    public function prepareForValidation()
    {
        $param = $this->input(config('define.master.session_key_id'));
        if (empty($param)) {
            throw new InvalidParameterException('Invalid parameter');
        }
        $previousData = $this->esmSessionManager->getSessionKeyName(
            config('define.master.warehouses_register_request'),
            config('define.session_key_id'),
            $param
        );
        $this->merge(
            $previousData
        );
    }
}
