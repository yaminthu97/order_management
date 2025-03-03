<?php

namespace App\Http\Requests\Customer\Gfh1207;

use App\Http\Requests\Customer\Base\CustCommunicationRequest as BaseCustCommunicationRequest;

class CustCommunicationRequest extends BaseCustCommunicationRequest
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
            't_cust_communication_id'   =>      ['nullable', 'numeric'],
            'm_cust_id'                 =>      ['nullable', 'numeric'],
            't_order_hdr_id'            =>      ['nullable', 'numeric'],
            'page_cd'                   =>      ['nullable', 'string', 'max:100'],
            'contact_way_type'          =>      ['required', 'numeric'],
            'name_kanji'                =>      ['nullable', 'string', 'max:100'],
            'name_kana'                 =>      ['nullable', 'string', 'max:100'],
            'tel'                       =>      ['nullable', 'max:20', 'tel'],
            'email'                     =>      ['nullable', 'max:100', 'email'],
            'postal'                    =>      ['nullable', 'max:7', 'postal7only'],
            'address1'                  =>      ['nullable', 'string', 'max:100'],
            'address2'                  =>      ['nullable', 'string', 'max:100'],
            'note'                      =>      ['nullable', 'string', 'max:100'],
            'title'                     =>      ['required', 'string', 'max:100'],
            'sales_channel'             =>      ['nullable', 'numeric'],
            'inquiry_type'              =>      ['nullable', 'numeric'],
            'status'                    =>      ['nullable', 'numeric'],
            'category'                  =>      ['nullable', 'numeric'],
            'receive_detail'            =>      ['required', 'string', 'max:16777215'],
            'receive_operator_id'       =>      ['required', 'numeric'],
            'receive_datetime'          =>      ['required', 'date'],
            'escalation_operator_id'    =>      ['nullable', 'numeric'],
            'answer_detail'             =>      ['nullable', 'string', 'max:16777215'],
            'answer_operator_id'        =>      ['nullable', 'numeric'],
            'answer_datetime'           =>      ['nullable', 'date'],
            'resolution_status'         =>      ['nullable', 'numeric'],
            'open'                      =>      ['required', 'in:0,1']
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'entry_operator_id' => $this->esmSessionManager->getOperatorId(),
            'update_operator_id' => $this->esmSessionManager->getOperatorId()
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
            'page_cd'                  =>  '商品ページコード',
            'name_kanji'               =>  '名前',
            'name_kana'                =>  'フリガナ',
            'address2'                 =>  '住所',
            'note'                     =>  '連絡先その他',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'tel.tel'                => ':attributeには正しい電話番号を入力してください。',
            'postal.postal7only'     => ':attributeには正しい郵便番号を入力してください。',
        ];
    }
}
