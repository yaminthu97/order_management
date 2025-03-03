<?php

namespace App\Http\Requests\Gfh1207;

use Illuminate\Foundation\Http\FormRequest;

class ImportEcbeingOrderDataRequest extends FormRequest
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
            //order_hdr
            'ec_order_num'  =>      ['required', 'string', 'max:100'],
            'order_date'  =>      ['required', 'regex:/^\d+$/', 'max:8'],
            'order_time'  =>      ['required', 'regex:/^\d+$/', 'max:6'],
            //'m_cust_id' => ['required', 'integer', 'digits_between:1,30'],
            'order_corporate_name'  =>      ['required', 'string', 'max:100'],
            'order_division_name'  =>      ['required', 'string', 'max:100'],
            'order_name1'  =>      ['required', 'string', 'max:100'],
            'order_name2'  =>      ['required', 'string', 'max:100'],
            'order_name_kana1' => ['required', 'string', 'max:50'],
            'order_name_kana2' =>  ['required', 'string', 'max:50'],
            'order_postal'  =>      ['required', 'string', 'max:7'],
            'order_address1'  =>      ['required', 'string', 'max:10'],
            'order_address2'  =>      ['required', 'string', 'max:100'],
            'order_address3'  =>      ['required', 'string', 'max:100'],
            'order_tel1'  =>      ['required', 'string', 'max:30'],
            'sell_total_price'  =>      ['required', 'integer', 'digits_between:1,13'],
            'order_hdr_shipping_fee'  =>      ['required', 'integer', 'digits_between:1,13'],
            'order_total_price'  =>      ['required', 'integer', 'digits_between:1,13'],
            'order_hdr_payment_fee'  =>      ['required', 'integer', 'digits_between:1,13'],
            'delivery_type_fee'  =>      ['required', 'integer', 'digits_between:1,13'],
            'm_payment_types_id'  =>      ['required', 'integer', 'digits_between:1,4'],
            'receipt_type'  =>      ['required', 'integer', 'digits_between:1,1'],
            'standard_total_price' => ['required', 'integer', 'digits_between:1,13'],
            'reduce_total_price' => ['required', 'integer', 'digits_between:1,13'],
            'standard_tax_price' => ['required', 'integer', 'digits_between:1,13'],
            'reduce_tax_price' => ['required', 'integer', 'digits_between:1,13'],

            //order_destination
            'order_destination_seq' =>      ['required', 'integer', 'digits_between:1,6'],
            'destination_name1' =>      ['required', 'string', 'max:50'],
            'destination_name2' =>      ['required', 'string', 'max:50'],
            'destination_postal' =>      ['required', 'string', 'max:7'],
            'destination_address1' =>      ['required', 'string', 'max:8'],
            'destination_address2' =>      ['required', 'string', 'max:11'],
            'destination_address3' =>      ['required', 'string', 'max:100'],
            'destination_tel' =>      ['required', 'string', 'max:30'],
            'total_temperature_zone_type' =>      ['required', 'integer', 'in:0,1,2'],
            'order_destination_shipping_fee' =>      ['required', 'integer', 'digits_between:1,13'],
            'order_destination_payment_fee' =>      ['required', 'integer', 'digits_between:1,13'],
            'campaign_flag' => ['required', 'integer', 'in:0,1'],

            //order_detail
            'sell_cd' =>      ['required', 'string', 'max:50'],
            'order_sell_vol' =>      ['required', 'integer', 'digits_between:1,11'],
            'order_sell_price' =>      ['required', 'integer', 'digits_between:1,10'],
            'tax_rate' => ['required', 'integer', 'digits_between:1,1']
        ];
    }
}
