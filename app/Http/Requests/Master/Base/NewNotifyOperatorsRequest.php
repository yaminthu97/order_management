<?php

namespace App\Http\Requests\Master\Base;

use Illuminate\Foundation\Http\FormRequest;

class NewNotifyOperatorsRequest extends FormRequest
{
    public function __construct(
        protected \App\Services\EsmSessionManager $esmSessionManager
    ) {
    }

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
            //
        ];
    }
}
