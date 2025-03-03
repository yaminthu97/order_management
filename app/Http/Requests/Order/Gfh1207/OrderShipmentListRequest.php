<?php

namespace App\Http\Requests\Order\Gfh1207;

use Illuminate\Foundation\Http\FormRequest;

class OrderShipmentListRequest extends FormRequest
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
			'process_type'  =>   [ 'required',	'in:1,2,3' ],
			'order_date_from'  =>  [ 'nullable',	'date' ],
			'order_date_to'  =>  [ 'nullable',	'date' ],
			'deli_plan_date_from'  => [ 'required',	'date' ],
			'deli_plan_date_to'  =>  [ 'nullable',	'date' ],
			'inspection_date_from'  =>  [ 'nullable',	'date' ],
			'inspection_date_to'  =>  [ 'nullable',	'date' ],  
			'order_id_from'  =>   [ 'nullable',	'numeric' ,'str_length_max_count:20' ],
			'order_id_to'  =>   [ 'nullable',	'numeric', 'str_length_max_count:20', 'gte:order_id_from'],
			'one_item_only'  =>   [ 'required',	'in:0,1' ],
			'has_noshi'  =>   [ 'nullable',	'in:0,1' ],
			'page_cd'  =>   ['nullable' , 'regex:/^[a-zA-Z0-9]+$/' , 'max:50', 'exists:m_ami_page,page_cd'],
			'store_group'  =>   [ 'nullable',	'numeric' ],
			'order_type'  =>   [ 'nullable',	'numeric' ],
		];
		
	}

	 /**
	 * 項目名
	 */
	public function attributes()
	{
		return [
			'page_cd' => '商品ページコード'
		];
	}
}
