<?php

namespace App\Http\Requests\Master\Gfh1207;

use App\Http\Requests\Master\Base\NewOperatorsRequest as BaseNewOperatorsRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NewOperatorsRequest extends BaseNewOperatorsRequest
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
        try {
            // Try to check if the DB connection is available
            DB::connection()->getPdo();
            $uniqueRule = Rule::unique('m_operators');
            $existsRule = 'exists:m_operation_authority,m_operation_authority_id,delete_flg,0';
        } catch (\Exception $e) {
            $uniqueRule = [];
            $existsRule = '';
        }

        return [
            'delete_flg'                        => ['required', 'integer', Rule::in(array_column(\App\Enums\DeleteFlg::cases(), 'value'))],
            'm_operator_name'                   => ['required', 'string', 'max:100'],
            'm_operator_email'                  => ['nullable', 'email', 'max:100'],
            'user_type'                         => ['required', 'integer', Rule::in(array_column(\App\Modules\Master\Gfh1207\Enums\UserTypeEnum::cases(), 'value'))],
            'm_operation_authority_id'          => ['required', 'integer', $existsRule],
            'cc_authority_code'                 => ['required', 'integer', Rule::in(array_column(\App\Modules\Customer\Gfh1207\Enums\AuthorityCode::cases(), 'value'))],
            'login_id'                          => ['required', 'string', 'regex:/^[a-zA-Z0-9\!\#\$\@\?\_\-\%\&]+$/', 'max:20', $uniqueRule],
            'login_password'                    => ['required', 'string', 'regex:/^(?=.*?[a-z])(?=.*?[A-Z])(?=.*?\d)(?=.*?[\!\@\#\$\%\^\&\*\(\)\_\+\-\=\[\]\{\}])[a-zA-Z0-9\!\#\$\@\?\_\-\%\&]+$/', 'min:12', 'max:255', 'confirmed'],
            'login_password_confirmation'       => ['required', 'string', 'regex:/^(?=.*?[a-z])(?=.*?[A-Z])(?=.*?\d)(?=.*?[\!\@\#\$\%\^\&\*\(\)\_\+\-\=\[\]\{\}])[a-zA-Z0-9\!\#\$\@\?\_\-\%\&]+$/', 'min:12', 'max:255'],
            'g2fa_key'                          => ['required', 'integer', Rule::in(array_column(\App\Modules\Master\Gfh1207\Enums\MultiFactorAuthentication::cases(), 'value'))]
        ];
    }


    public function prepareForValidation()
    {
        $this->merge([
            'operator_id' => $this->esmSessionManager->getOperatorId(),
        ]);
    }
}
