<?php

namespace App\Http\Requests\Order\Gfh1207;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentReportsRequest extends FormRequest
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
            'deli_plan_date_from'  => [ 'nullable',	'date' ],
            'deli_plan_date_to'  =>  [ 'nullable',	'date' ,'after_or_equal:deli_plan_date_from'],
            'deli_inspection_date_from'  => [ 'nullable',	'date' ],
            'deli_inspection_date_to'  =>  [ 'nullable',	'date' ,'after_or_equal:deli_inspection_date_from'],
            'deli_plan_date_from1'  => [ 'nullable',	'date' ],
            'deli_plan_date_to1'  =>  [ 'nullable',	'date' ,'after_or_equal:deli_plan_date_from1'],
            'deli_plan_date_from3'  => [ 'nullable',	'date' ],
            'deli_plan_date_to3'  =>  [ 'nullable',	'date' ,'after_or_equal:deli_plan_date_from3'],
            'order_id_from'  =>   [ 'nullable',	'numeric' ,'str_length_max_count:20' ],
            'order_id_to'  =>   [ 'nullable',	'numeric', 'str_length_max_count:20', 'gte:order_id_from'],

        ];

    }

    /**
    * 項目名
    */
    public function attributes()
    {
        return [
            'deli_plan_date_from' => '出荷予定日FROM',
            'deli_plan_date_to' => '出荷予定日TO',
            'deli_inspection_date_from' => '検品日FROM',
            'deli_inspection_date_to' => '検品日TO',
            'deli_plan_date_from1' => '出荷予定日FROM',
            'deli_plan_date_to1' => '出荷予定日TO',
            'deli_plan_date_from3' => '出荷予定日FROM',
            'deli_plan_date_to3' => '出荷予定日TO',
            'order_id_from' => '受注IDFROM',
            'order_id_to' => '受注IDTO',
        ];
    }
}
