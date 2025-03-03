<?php

namespace App\Http\Requests\Sample\Base;

use Illuminate\Foundation\Http\FormRequest;

class EditSampleRequest extends FormRequest
{

    public function __construct(
        protected \App\Services\EsmSessionManager $esmSessionManager
    )
    {}

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
            'name_sorting_flg'        => [ 'nullable', 'in:0,1' , ],
            'm_cust_id'               => [ 'nullable', 'exists:local.m_cust,m_cust_id', ],
            'cust_cd'                 => [ 'nullable', 'max:255', ],
            'm_cust_runk_id'          => [ 'nullable', 'numeric' , ],
            'name_kanji'              => [ 'required', 'max:100', ],
            'name_kana'               => [ 'nullable', 'max:100', ],
            'sex_type'                => [ 'nullable', 'in:0,1,2' , ],
            'birthday'                => [ 'nullable', 'date' , ],
            'tel1'                    => [ 'nullable', 'max:20'  ,],
            'tel2'                    => [ 'nullable', 'max:20'  ,],
            'tel3'                    => [ 'nullable', 'max:20'  ,],
            'tel4'                    => [ 'nullable', 'max:20'  ,],
            'fax'                     => [ 'nullable', 'max:20'  ,],
            'postal'                  => [ 'nullable', 'max:8'   , 'postal', ],
            'address1'                => [ 'required', 'max:100', ],
            'address2'                => [ 'required', 'max:100', ],
            'address3'                => [ 'nullable', 'max:100', ],
            'address4'                => [ 'nullable', 'max:100', ],
            'address5'                => [ 'nullable', 'max:100', ],
            'corporate_kanji'         => [ 'nullable', 'max:100', ],
            'corporate_kana'          => [ 'nullable', 'max:100', ],
            'division_name'           => [ 'nullable', 'max:100', ],
            'corporate_tel'           => [ 'nullable', 'max:20'  ,],
            'email1'                  => [ 'nullable', 'max:255' , 'email_notrfc', ],
            'email2'                  => [ 'nullable', 'max:255' , 'email_notrfc', ],
            'email3'                  => [ 'nullable', 'max:255' , 'email_notrfc', ],
            'email4'                  => [ 'nullable', 'max:255' , 'email_notrfc', ],
            'email5'                  => [ 'nullable', 'max:255' , 'email_notrfc', ],
            'alert_cust_type'         => [ 'nullable', 'in:0,1,2' , ],
            'alert_cust_comment'      => [ 'nullable', ],
            'note'                    => [ 'nullable', ],
            'reserve1'                => [ 'nullable', ],
            'reserve2'                => [ 'nullable', 'max:100', ],
            'reserve3'                => [ 'nullable', 'max:100', ],
            'reserve4'                => [ 'nullable', 'max:100', ],
            'reserve5'                => [ 'nullable', 'max:100', ],
            'reserve6'                => [ 'nullable', 'max:100', ],
            'reserve7'                => [ 'nullable', 'max:100', ],
            'reserve8'                => [ 'nullable', 'max:100', ],
            'reserve9'                => [ 'nullable', 'max:100', ],
            'reserve10'               => [ 'nullable', 'max:100', ],
            'reserve11'               => [ 'nullable', 'max:100', ],
            'reserve12'               => [ 'nullable', 'max:100', ],
            'reserve13'               => [ 'nullable', 'max:100', ],
            'reserve14'               => [ 'nullable', 'max:100', ],
            'reserve15'               => [ 'nullable', 'max:100', ],
            'reserve16'               => [ 'nullable', 'max:100', ],
            'reserve17'               => [ 'nullable', 'max:100', ],
            'reserve18'               => [ 'nullable', 'max:100', ],
            'reserve19'               => [ 'nullable', 'max:100', ],
            'reserve20'               => [ 'nullable', 'max:100', ],
            'delete_flg'              => [ 'nullable', 'in:0,1' , ],
        ];
    }
}
